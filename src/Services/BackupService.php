<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Services;

use Contensio\ContensioServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ZipArchive;

class BackupService
{
    const BACKUP_DIR   = 'backups';
    const MANIFEST     = 'manifest.json';
    const DB_FILE      = 'database.sql';
    const STORAGE_DIR  = 'app';

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Create a new backup ZIP.
     *
     * @param  bool $includeFiles  Whether to include uploaded media files.
     * @return string              Absolute path to the created ZIP file.
     */
    public function create(bool $includeFiles = true): string
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $this->ensureBackupDir();

        $hostname = Str::slug(parse_url(config('app.url'), PHP_URL_HOST) ?? 'site');
        $suffix   = $includeFiles ? 'full' : 'db';
        $filename = sprintf('backup-%s-%s-%s.zip', $hostname, now()->format('Ymd-His'), $suffix);
        $zipPath  = $this->backupDirPath() . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Cannot create backup file at {$zipPath}");
        }

        // --- Database dump ---
        $sqlTmp = sys_get_temp_dir() . '/contensio-db-' . uniqid() . '.sql';
        $tableCount = $this->dumpDatabase($sqlTmp);
        $zip->addFile($sqlTmp, self::DB_FILE);

        // --- Media files ---
        $fileCount = 0;
        if ($includeFiles) {
            $storageBase = storage_path(self::STORAGE_DIR);
            $fileCount   = $this->addDirectoryToZip($zip, $storageBase, self::STORAGE_DIR, [
                self::STORAGE_DIR . '/' . self::BACKUP_DIR,
            ]);
        }

        // --- Manifest ---
        $manifest = [
            'product'           => 'contensio',
            'format_version'    => '1.0',
            'created_at'        => now()->toIso8601String(),
            'site_url'          => config('app.url'),
            'cms_version'       => ContensioServiceProvider::VERSION,
            'includes_database' => true,
            'includes_files'    => $includeFiles,
            'table_count'       => $tableCount,
            'file_count'        => $fileCount,
        ];
        $zip->addFromString(self::MANIFEST, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $zip->close();

        // Clean up temp SQL file (after zip->close so it's flushed)
        @unlink($sqlTmp);

        return $zipPath;
    }

    /**
     * List all backup files, newest first.
     *
     * @return array<int, array{filename: string, path: string, size: int, size_human: string, created_at: string, includes_files: bool}>
     */
    public function list(): array
    {
        $this->ensureBackupDir();

        $files = glob($this->backupDirPath() . '/backup-*.zip');
        if (! $files) return [];

        $backups = [];
        foreach ($files as $path) {
            $manifest = $this->readManifest($path);
            $backups[] = [
                'filename'      => basename($path),
                'path'          => $path,
                'size'          => filesize($path),
                'size_human'    => $this->humanSize(filesize($path)),
                'created_at'    => $manifest['created_at'] ?? filectime($path),
                'includes_files'=> (bool) ($manifest['includes_files'] ?? false),
                'cms_version'   => $manifest['cms_version'] ?? '?',
                'site_url'      => $manifest['site_url'] ?? '?',
                'table_count'   => $manifest['table_count'] ?? 0,
                'file_count'    => $manifest['file_count'] ?? 0,
            ];
        }

        usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    /**
     * Delete a backup file by filename (basename only — prevents path traversal).
     */
    public function delete(string $filename): void
    {
        $path = $this->resolveBackupPath($filename);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Validate a ZIP and return its manifest. Throws on invalid files.
     *
     * @return array  The manifest data.
     */
    public function validate(string $zipPath): array
    {
        if (! file_exists($zipPath)) {
            throw new \RuntimeException('Backup file not found.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Cannot open backup file — it may be corrupted.');
        }

        $manifestJson = $zip->getFromName(self::MANIFEST);
        $zip->close();

        if (! $manifestJson) {
            throw new \RuntimeException('Not a valid Contensio backup — manifest missing.');
        }

        $manifest = json_decode($manifestJson, true);

        if (($manifest['product'] ?? '') !== 'contensio') {
            throw new \RuntimeException('Not a valid Contensio backup file.');
        }

        return $manifest;
    }

    /**
     * Restore a backup. Returns a summary array.
     *
     * @return array{tables: int, files: int}
     */
    public function restore(string $zipPath): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $manifest = $this->validate($zipPath);

        $zip = new ZipArchive();
        $zip->open($zipPath);

        $tmpDir   = sys_get_temp_dir() . '/contensio-restore-' . uniqid();
        mkdir($tmpDir, 0755, true);

        try {
            $zip->extractTo($tmpDir);
            $zip->close();

            $tableCount = 0;
            $fileCount  = 0;

            // --- Restore database ---
            $sqlFile = $tmpDir . '/' . self::DB_FILE;
            if (file_exists($sqlFile)) {
                $tableCount = $this->restoreDatabase($sqlFile);
            }

            // --- Restore files ---
            if ($manifest['includes_files'] ?? false) {
                $srcDir = $tmpDir . '/' . self::STORAGE_DIR;
                if (is_dir($srcDir)) {
                    $fileCount = $this->restoreFiles($srcDir, storage_path(self::STORAGE_DIR));
                }
            }

            return ['tables' => $tableCount, 'files' => $fileCount];
        } finally {
            $this->removeDirectory($tmpDir);
        }
    }

    /**
     * Absolute path to a specific backup file (validates filename is just a basename).
     */
    public function resolveBackupPath(string $filename): string
    {
        // Strip any directory components — never allow path traversal
        $safe = basename($filename);

        if (! preg_match('/^backup-.+\.zip$/', $safe)) {
            throw new \InvalidArgumentException('Invalid backup filename.');
        }

        return $this->backupDirPath() . '/' . $safe;
    }

    // -------------------------------------------------------------------------
    // Dump
    // -------------------------------------------------------------------------

    /**
     * Write a full SQL dump to $outputFile.
     * Returns the number of tables dumped.
     */
    private function dumpDatabase(string $outputFile): int
    {
        $pdo    = DB::connection()->getPdo();
        $fh     = fopen($outputFile, 'w');

        fwrite($fh, "-- Contensio full database backup\n");
        fwrite($fh, "-- Created: " . now()->toIso8601String() . "\n");
        fwrite($fh, "-- Database: " . DB::getDatabaseName() . "\n\n");
        fwrite($fh, "SET NAMES utf8mb4;\n");
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables     = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        $tableCount = count($tables);

        foreach ($tables as $table) {
            fwrite($fh, "-- --------------------------------------------------------\n");
            fwrite($fh, "-- Table: `{$table}`\n");
            fwrite($fh, "-- --------------------------------------------------------\n\n");
            fwrite($fh, "DROP TABLE IF EXISTS `{$table}`;\n");

            // CREATE TABLE
            $row       = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
            $createSql = $row['Create Table'] ?? $row['create table'] ?? '';
            fwrite($fh, $createSql . ";\n\n");

            // INSERT rows — stream one by one to avoid memory spikes
            $stmt = $pdo->query("SELECT * FROM `{$table}`");
            $colNames = null;

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if ($colNames === null) {
                    $colNames = '`' . implode('`, `', array_keys($row)) . '`';
                }
                $values = array_map(function ($val) use ($pdo) {
                    if ($val === null) return 'NULL';
                    return $pdo->quote((string) $val);
                }, array_values($row));

                fwrite($fh, "INSERT INTO `{$table}` ({$colNames}) VALUES (" . implode(', ', $values) . ");\n");
            }

            fwrite($fh, "\n");
        }

        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);

        return $tableCount;
    }

    // -------------------------------------------------------------------------
    // Restore
    // -------------------------------------------------------------------------

    /**
     * Execute the SQL dump file against the current database connection.
     * Returns the number of tables restored.
     */
    private function restoreDatabase(string $sqlFile): int
    {
        $pdo = DB::connection()->getPdo();

        $fh          = fopen($sqlFile, 'r');
        $buffer      = '';
        $tableCount  = 0;

        while (($line = fgets($fh)) !== false) {
            $trimmed = rtrim($line);

            // Skip pure comment lines and blanks (but accumulate them in buffer for CREATE TABLE)
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $buffer .= $line;

            // Execute when we hit a statement terminator at the end of a line
            if (str_ends_with($trimmed, ';')) {
                $sql = trim($buffer);
                if ($sql && $sql !== ';') {
                    $pdo->exec($sql);
                    if (stripos($sql, 'CREATE TABLE') !== false) {
                        $tableCount++;
                    }
                }
                $buffer = '';
            }
        }

        fclose($fh);

        return $tableCount;
    }

    /**
     * Recursively copy files from $src to $dst.
     * Returns the number of files copied.
     */
    private function restoreFiles(string $src, string $dst): int
    {
        $count = 0;
        $it    = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $item) {
            $target = $dst . '/' . $it->getSubPathname();

            if ($item->isDir()) {
                @mkdir($target, 0755, true);
            } else {
                @mkdir(dirname($target), 0755, true);
                copy($item->getPathname(), $target);
                $count++;
            }
        }

        return $count;
    }

    // -------------------------------------------------------------------------
    // ZIP helpers
    // -------------------------------------------------------------------------

    /**
     * Add a directory to a ZIP archive recursively.
     * $skipPrefixes is a list of ZIP-internal path prefixes to exclude (without leading /).
     * Returns the number of files added.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPrefix, array $skipPrefixes = []): int
    {
        if (! is_dir($dir)) return 0;

        $count = 0;
        $it    = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $item) {
            $zipPath = $zipPrefix . '/' . $it->getSubPathname();
            $zipPath = str_replace('\\', '/', $zipPath);

            // Skip excluded prefixes (e.g. the backups dir itself)
            foreach ($skipPrefixes as $skip) {
                if (str_starts_with($zipPath, $skip)) {
                    continue 2;
                }
            }

            if ($item->isFile()) {
                $zip->addFile($item->getPathname(), $zipPath);
                $count++;
            }
        }

        return $count;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function backupDirPath(): string
    {
        return storage_path(self::STORAGE_DIR . '/' . self::BACKUP_DIR);
    }

    private function ensureBackupDir(): void
    {
        $path = $this->backupDirPath();
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function readManifest(string $zipPath): array
    {
        try {
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) return [];
            $json = $zip->getFromName(self::MANIFEST);
            $zip->close();
            return $json ? (json_decode($json, true) ?? []) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) return;
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
