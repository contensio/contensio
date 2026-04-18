<?php

/**
 * Contensio - The open content platform for Laravel.
 * CLI — create a site backup.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Console\Commands;

use Contensio\Services\BackupService;
use Illuminate\Console\Command;

class BackupCommand extends Command
{
    protected $signature = 'contensio:backup
        {--no-files : Skip uploaded media files — database only}
    ';

    protected $description = 'Create a full site backup (database + media files) as a ZIP archive.';

    public function handle(BackupService $backups): int
    {
        $includeFiles = ! $this->option('no-files');

        $this->line('');
        $this->info('Creating backup…');

        if (! $includeFiles) {
            $this->line('  <comment>--no-files</comment> set — skipping media files.');
        }

        try {
            $path = $backups->create($includeFiles);
        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $size = filesize($path);
        $sizeHuman = $this->humanSize($size);

        $this->line('');
        $this->line("  <info>✓</info> Backup created: <comment>{$path}</comment>");
        $this->line("    Size: {$sizeHuman}");
        $this->line('');

        return self::SUCCESS;
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
}
