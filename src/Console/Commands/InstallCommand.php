<?php

/**
 * Contensio - The open content platform for Laravel.
 * CLI installer — runs the same seed + admin-creation flow as the web installer.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * LICENSE:
 * Permissions of this strongest copyleft license are conditioned on making
 * available complete source code of licensed works and modifications, which
 * include larger works using a licensed work, under the same license.
 * Copyright and license notices must be preserved. Contributors provide an
 * express grant of patent rights. When a modified version is used to provide
 * a service over a network, the complete source code of the modified version
 * must be made available.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Console\Commands;

use Contensio\Services\Install\Installer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class InstallCommand extends Command
{
    protected $signature = 'contensio:install
        {--site-name=      : Site name (e.g. "My Contensio Site")}
        {--language=       : Default language code (e.g. en, ro, de)}
        {--admin-name=     : Admin full name}
        {--admin-email=    : Admin email address}
        {--admin-password= : Admin password (min 8 chars)}
        {--skip-migrate    : Skip running migrations — useful if they already ran}
        {--force           : Proceed even if CONTENSIO_INSTALLED is already true}
    ';

    protected $description = 'Install Contensio — publishes assets, runs migrations, seeds defaults, and creates the first admin.';

    public function handle(Installer $installer): int
    {
        $this->line('');
        $this->info('Contensio installer');
        $this->line('');

        // ── Guard: already installed? ──────────────────────────────────
        if (env('CONTENSIO_INSTALLED', false) && ! $this->option('force')) {
            $this->warn('Contensio is already installed (CONTENSIO_INSTALLED=true in .env).');
            $this->line('  Re-run with --force to proceed anyway (useful for resetting the admin account).');
            return self::FAILURE;
        }

        // ── 1. Publish + migrate ───────────────────────────────────────
        $this->line('  Publishing config + assets…');
        $this->call('vendor:publish', ['--tag' => 'contensio-config']);
        $this->call('vendor:publish', ['--tag' => 'contensio-assets']);

        if (! $this->option('skip-migrate')) {
            $this->line('  Running migrations…');
            $this->call('migrate', ['--force' => true]);
        }

        // ── 2. Gather setup inputs (flags take precedence over prompts) ─
        $siteName = $this->collect('site-name',  'Site name',       fn () => config('app.name', 'My Contensio Site'));
        $language = $this->collectLanguage($installer);

        $adminName     = $this->collect('admin-name',  'Admin full name', fn () => null, required: true);
        $adminEmail    = $this->collectEmail();
        $adminPassword = $this->collectPassword();

        // ── 3. Seed the world ──────────────────────────────────────────
        $this->line('');
        $this->line('  Seeding defaults (languages, settings, content types, taxonomies, roles, blocks)…');

        $userId = $installer->bootstrap(
            siteName:      $siteName,
            languageCode:  $language,
            adminName:     $adminName,
            adminEmail:    $adminEmail,
            adminPassword: $adminPassword,
        );

        $installer->markInstalled();

        // ── 4. Summary ─────────────────────────────────────────────────
        $this->line('');
        $this->info('Contensio installed successfully.');
        $this->line('');
        $this->line('  Admin URL:  ' . url(config('contensio.route_prefix', 'admin')));
        $this->line('  Admin user: ' . $adminEmail . '  (user #' . $userId . ')');
        $this->line('');
        $this->comment('  Sign in at /login with the credentials you just set.');
        $this->line('');

        return self::SUCCESS;
    }

    /* ────────────────────────── prompt helpers ─────────────────────── */

    /**
     * Read a value from the flag or prompt the user if missing.
     * Non-interactive environments (flags all set) need zero input.
     */
    protected function collect(string $option, string $label, ?\Closure $defaultFn = null, bool $required = false): string
    {
        $value = $this->option($option);
        if ($value !== null && $value !== '') return $value;

        $default = $defaultFn ? $defaultFn() : null;

        do {
            $value = $this->ask($label, $default);
            if (! $required || $value !== null && $value !== '') break;
            $this->error($label . ' is required.');
        } while (true);

        return (string) $value;
    }

    protected function collectLanguage(Installer $installer): string
    {
        $choices = $installer->availableLanguages();

        $flag = $this->option('language');
        if ($flag) {
            if (! isset($choices[$flag])) {
                $this->error("Unknown language code: {$flag}. Available: " . implode(', ', array_keys($choices)));
                exit(self::FAILURE);
            }
            return $flag;
        }

        $labels  = array_values(array_map(fn ($name, $code) => "{$code} — {$name}", $choices, array_keys($choices)));
        $picked  = $this->choice('Default language', $labels, 0);
        return explode(' —', $picked)[0];
    }

    protected function collectEmail(): string
    {
        $value = $this->option('admin-email');

        do {
            if (! $value) {
                $value = $this->ask('Admin email');
            }
            $validator = Validator::make(['email' => $value], ['email' => 'required|email']);
            if (! $validator->fails()) return (string) $value;

            $this->error('Invalid email address.');
            $value = null;
        } while (true);
    }

    protected function collectPassword(): string
    {
        $value = $this->option('admin-password');

        do {
            if (! $value) {
                $value = $this->secret('Admin password (min 8 chars)');
            }
            if (strlen((string) $value) >= 8) return (string) $value;

            $this->error('Password must be at least 8 characters.');
            $value = null;
        } while (true);
    }
}
