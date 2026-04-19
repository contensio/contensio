<?php

/**
 * Contensio - The open content platform for Laravel.
 * White-label branding service.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Services;

use Contensio\Models\Setting;

/**
 * Provides white-label branding URLs and license status for admin layouts.
 *
 * SECURITY NOTE:
 * Activation status is determined by verifying the Ed25519 signature of the
 * stored license key on every request. We never trust a cached "status" string
 * in the database — a DB row can be forged with a direct SQL INSERT, but a
 * valid cryptographic signature cannot be forged without the private key that
 * lives only on contensio.com.
 *
 * The verification result is cached in a static property for the duration of
 * the request (one DB read + one Ed25519 check per page load).
 */
class WhitelabelService
{
    /** Per-request cache: null = not yet checked, false = inactive, true = active. */
    private static ?bool  $active  = null;
    private static ?array $payload = null;
    private static ?array $config  = null;

    /**
     * Whether a valid, non-expired, domain-matched white-label license is active.
     *
     * Reads the raw license key from the DB and verifies its Ed25519 signature
     * every request. Forging the DB status field has no effect.
     */
    public static function isActive(): bool
    {
        if (self::$active !== null) {
            return self::$active;
        }

        try {
            $key = Setting::where('module', 'whitelabel')
                ->where('setting_key', 'license_key')
                ->value('value');

            if (empty($key)) {
                return self::$active = false;
            }

            $result = LicenseService::parse($key);

            if (! $result['valid']) {
                return self::$active = false;
            }

            self::$payload = $result['payload'];
            return self::$active = true;

        } catch (\Throwable) {
            return self::$active = false;
        }
    }

    /**
     * Return the validated license payload (only available when isActive() === true).
     */
    public static function payload(): ?array
    {
        self::isActive(); // ensure checked
        return self::$payload;
    }

    /**
     * URL for the dark-sidebar admin logo.
     */
    public static function adminLogoDarkUrl(): string
    {
        if (self::isActive()) {
            $url = self::setting('admin_logo_dark_url');
            if ($url) return $url;
        }

        return asset(config('contensio.admin_logo_dark', 'vendor/contensio/img/logo-backend.png'));
    }

    /**
     * URL for the auth-page logo (login, register).
     */
    public static function adminLogoUrl(): string
    {
        if (self::isActive()) {
            $url = self::setting('admin_logo_url');
            if ($url) return $url;
        }

        return asset(config('contensio.admin_logo', 'vendor/contensio/img/logo.png'));
    }

    /**
     * URL for the admin favicon.
     */
    public static function adminFaviconUrl(): string
    {
        if (self::isActive()) {
            $url = self::setting('admin_favicon_url');
            if ($url) return $url;
        }

        return asset(config('contensio.admin_favicon', 'vendor/contensio/img/favicon128x128.png'));
    }

    /**
     * Whether to suppress the "Powered by Contensio" line on auth pages.
     */
    public static function hidePoweredBy(): bool
    {
        return self::isActive() && self::setting('hide_powered_by') === '1';
    }

    /**
     * Admin panel name — replaces "Contensio" in the <title> tag and footer.
     */
    public static function adminName(): string
    {
        if (self::isActive()) {
            $name = self::setting('admin_name');
            if ($name) return $name;
        }

        return config('contensio.name', 'Contensio');
    }

    /**
     * Whether to hide the admin footer bar entirely (name + version).
     */
    public static function hideAdminFooter(): bool
    {
        return self::isActive() && self::setting('hide_admin_footer') === '1';
    }

    /**
     * Accent color for interactive elements (buttons, links, rings).
     * Returns a CSS hex value — defaults to ember-500.
     */
    public static function accentColor(): string
    {
        if (self::isActive()) {
            $color = self::setting('accent_color');
            if ($color) return $color;
        }

        return '#d04a1f';
    }

    /**
     * Darker accent shade used for hover states.
     */
    public static function accentDarkColor(): string
    {
        if (self::isActive()) {
            $color = self::setting('accent_dark_color');
            if ($color) return $color;
        }

        return '#b23e18';
    }

    /**
     * Sidebar background color — defaults to slate-900.
     */
    public static function sidebarColor(): string
    {
        if (self::isActive()) {
            $color = self::setting('sidebar_bg_color');
            if ($color) return $color;
        }

        return '#0f172a';
    }

    /**
     * Background color for the auth / login pages.
     */
    public static function loginBgColor(): string
    {
        if (self::isActive()) {
            $color = self::setting('login_bg_color');
            if ($color) return $color;
        }

        return '#fbf8f0';
    }

    /**
     * Optional background image URL for auth pages.
     */
    public static function loginBgImageUrl(): ?string
    {
        if (self::isActive()) {
            return self::setting('login_bg_image_url') ?: null;
        }

        return null;
    }

    /**
     * Optional tagline displayed below the logo on auth pages.
     */
    public static function loginTagline(): ?string
    {
        if (self::isActive()) {
            return self::setting('login_tagline') ?: null;
        }

        return null;
    }

    /**
     * From-name used in transactional emails.
     */
    public static function emailSenderName(): string
    {
        if (self::isActive()) {
            $name = self::setting('email_sender_name');
            if ($name) return $name;
        }

        return config('app.name', 'Contensio');
    }

    /**
     * Custom footer text in transactional emails (replaces the default Contensio copy).
     */
    public static function emailFooterText(): ?string
    {
        if (self::isActive()) {
            return self::setting('email_footer_text') ?: null;
        }

        return null;
    }

    /**
     * Reset the per-request cache (called after saving/removing settings).
     */
    public static function flush(): void
    {
        self::$active  = null;
        self::$payload = null;
        self::$config  = null;
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    private static function setting(string $key): ?string
    {
        if (self::$config === null) {
            try {
                self::$config = Setting::where('module', 'whitelabel')
                    ->whereIn('setting_key', [
                        'admin_logo_url',
                        'admin_logo_dark_url',
                        'admin_favicon_url',
                        'hide_powered_by',
                        'admin_name',
                        'hide_admin_footer',
                        'email_sender_name',
                        'email_footer_text',
                        'accent_color',
                        'accent_dark_color',
                        'sidebar_bg_color',
                        'login_bg_color',
                        'login_bg_image_url',
                        'login_tagline',
                    ])
                    ->pluck('value', 'setting_key')
                    ->toArray();
            } catch (\Throwable) {
                self::$config = [];
            }
        }

        return self::$config[$key] ?? null;
    }
}
