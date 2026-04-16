<?php

/**
 * Contensio - The open content platform for Laravel.
 * Email / SMTP config — loads stored settings and overlays them onto
 * Laravel's mail config at runtime so users never have to edit .env.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 */

namespace Contensio\Cms\Support;

use Contensio\Cms\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class EmailConfig
{
    /**
     * Read all 'email' module settings, with the password decrypted.
     * Returns an associative array of setting_key → value.
     */
    public static function load(): array
    {
        try {
            $raw = Setting::where('module', 'email')->pluck('value', 'setting_key')->all();
        } catch (\Throwable) {
            $raw = [];
        }

        $defaults = [
            'mailer'       => 'smtp',
            'host'         => '',
            'port'         => '587',
            'encryption'   => 'tls',
            'username'     => '',
            'password'     => '', // decrypted (plain) — never expose raw in UI; form uses placeholder
            'from_address' => '',
            'from_name'    => config('cms.name', 'Contensio'),
        ];

        $out = array_merge($defaults, $raw);

        // Decrypt password if present
        if (! empty($out['password'])) {
            try {
                $out['password'] = Crypt::decryptString($out['password']);
            } catch (\Throwable) {
                // Legacy plaintext or corrupted — leave as-is
            }
        }

        return $out;
    }

    /**
     * Overlay stored settings onto Laravel's mail config. No-op if no SMTP
     * host is configured (falls back to whatever .env / config/mail.php set).
     */
    public static function apply(): void
    {
        $s = static::load();

        if (empty($s['host']) && $s['mailer'] === 'smtp') {
            return; // not configured yet
        }

        Config::set('mail.default', $s['mailer']);
        Config::set('mail.from.address', $s['from_address'] ?: config('mail.from.address'));
        Config::set('mail.from.name', $s['from_name'] ?: config('mail.from.name'));

        if ($s['mailer'] === 'smtp') {
            Config::set('mail.mailers.smtp.host',       $s['host']);
            Config::set('mail.mailers.smtp.port',       (int) ($s['port'] ?: 587));
            Config::set('mail.mailers.smtp.encryption', $s['encryption'] ?: null);
            Config::set('mail.mailers.smtp.username',   $s['username'] ?: null);
            Config::set('mail.mailers.smtp.password',   $s['password'] ?: null);
        }
    }
}
