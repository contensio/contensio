<?php

/**
 * Contensio - The open content platform for Laravel.
 * A flexible content foundation for blogs, shops, communities,
 * and any content-driven app.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Support;

use Contensio\Models\Setting;

/**
 * Site identity and branding values from the Core settings.
 *
 * Reads `site_name`, `site_tagline`, `site_logo`, and `site_favicon` from the
 * settings table and exposes them through static helpers and WordPress-style
 * global functions (see helpers.php).
 *
 * Results are memoized for the lifetime of the request so the table is queried
 * at most once — safe to call from layouts, view composers, or any theme file.
 *
 * ## Usage in theme templates
 *
 * ```blade
 * {{-- Favicon --}}
 * <link rel="icon" href="{{ site_favicon() ?: asset('vendor/contensio/img/favicon128x128.png') }}">
 *
 * {{-- Logo or site name --}}
 * @if(site_logo())
 *     <img src="{{ site_logo() }}" alt="{{ site_name() }}" class="h-8 w-auto">
 * @else
 *     <span>{{ site_name() }}</span>
 * @endif
 * ```
 *
 * ## Usage in PHP
 *
 * ```php
 * use Contensio\Support\SiteConfig;
 *
 * $all     = SiteConfig::all();    // ['name' => ..., 'tagline' => ..., 'logo' => ..., 'favicon' => ...]
 * $logo    = SiteConfig::get('logo');
 * $favicon = SiteConfig::get('favicon');
 * ```
 */
class SiteConfig
{
    /** Per-request memoization. */
    protected static ?array $cache = null;

    /**
     * All site identity values as a flat array.
     *
     * Keys: name, tagline, logo, favicon.
     */
    public static function all(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        try {
            $settings = Setting::where('module', 'core')
                ->whereIn('setting_key', ['site_name', 'site_tagline', 'site_logo', 'site_favicon'])
                ->pluck('value', 'setting_key');
        } catch (\Throwable) {
            // DB unavailable (installer, tests) — fall back entirely
            $settings = collect();
        }

        return static::$cache = [
            'name'    => $settings['site_name']    ?? config('app.name'),
            'tagline' => $settings['site_tagline'] ?? '',
            'logo'    => $settings['site_logo']    ?? '',
            'favicon' => $settings['site_favicon'] ?? '',
        ];
    }

    /**
     * A single value by key (name | tagline | logo | favicon).
     * Returns $default if the key isn't found.
     */
    public static function get(string $key, mixed $default = ''): mixed
    {
        return static::all()[$key] ?? $default;
    }

    /**
     * Bust the per-request cache — called after settings are saved so the
     * same request sees fresh values (e.g. on a redirect-after-save response).
     */
    public static function flush(): void
    {
        static::$cache = null;
    }
}
