<?php

/**
 * Contensio - The open content platform for Laravel.
 * CLI — restore a site from a backup ZIP.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Console\Commands;

use Contensio\Services\BackupService;
use Illuminate\Console\Command;

class RestoreCommand extends Command
{
    protected $signature = 'contensio:restore
        {file : Path to the backup ZIP file}
        {--force : Skip the confirmation prompt}
    ';

    protected $description = 'Restore a site from a Contensio backup ZIP (database + media files).';

    public function handle(BackupService $backups): int
    {
        $zipPath = $this->argument('file');

        // Validate first so we show manifest before asking for confirmation
        try {
            $manifest = $backups->validate($zipPath);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->line('');
        $this->line('  <comment>Backup details</comment>');
        $this->line('  Created at : ' . ($manifest['created_at'] ?? '?'));
        $this->line('  Site URL   : ' . ($manifest['site_url'] ?? '?'));
        $this->line('  Version    : ' . ($manifest['cms_version'] ?? '?'));
        $this->line('  Tables     : ' . ($manifest['table_count'] ?? '?'));
        $this->line('  Files      : ' . ($manifest['file_count'] ?? '?'));
        $this->line('  With files : ' . (($manifest['includes_files'] ?? false) ? 'yes' : 'no'));
        $this->line('');

        if (! $this->option('force')) {
            $confirmed = $this->confirm(
                '  <error>WARNING</error> This will overwrite the current database and files. Continue?',
                false
            );

            if (! $confirmed) {
                $this->line('  Restore cancelled.');
                return self::SUCCESS;
            }
        }

        $this->line('  Restoring…');

        try {
            $result = $backups->restore($zipPath);
        } catch (\Throwable $e) {
            $this->error('Restore failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->line('');
        $this->line("  <info>✓</info> Restore complete — {$result['tables']} tables, {$result['files']} files.");
        $this->line('');

        return self::SUCCESS;
    }
}
