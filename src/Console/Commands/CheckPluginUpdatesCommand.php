<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Console\Commands;

use Contensio\Services\PluginUpdateChecker;
use Illuminate\Console\Command;

class CheckPluginUpdatesCommand extends Command
{
    protected $signature = 'contensio:check-plugin-updates
                            {--force : Bypass the 24-hour cache and force a fresh check}';

    protected $description = 'Check all enabled plugins for available updates';

    public function handle(): int
    {
        if ($this->option('force')) {
            PluginUpdateChecker::clearCache();
            $this->line('Cache cleared. Fetching fresh update data...');
        }

        $updates = PluginUpdateChecker::refresh();

        if (empty($updates)) {
            $this->info('All plugins are up to date.');
            return self::SUCCESS;
        }

        $this->info(count($updates) . ' plugin update(s) available:');

        foreach ($updates as $name => $info) {
            $this->line("  • {$name} → v{$info['latest_version']}");
        }

        return self::SUCCESS;
    }
}
