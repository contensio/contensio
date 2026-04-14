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

namespace Contensio\Cms\Support;

use Composer\Autoload\ClassLoader;
use Contensio\Cms\Models\Setting;
use Contensio\Cms\Support\AccessControl;

/**
 * Central registry for all discovered plugins.
 *
 * Plugins come from two sources:
 *   1. Local   — extracted ZIPs in {project}/packages/plugins/{vendor}/{name}/
 *   2. Composer — packages in vendor/ with extra.cms.type = "plugin"
 *
 * Unlike themes (of which only ONE is active), many plugins can be enabled
 * simultaneously. The enabled list is stored in the database (core.enabled_plugins),
 * never in .env — so non-technical users can toggle plugins from the admin UI
 * without server access.
 */
class PluginRegistry
{
    /** @var array<string, array> Keyed by vendor/name. */
    protected static array $plugins = [];

    /**
     * Discover all available plugins from both sources and seed the registry.
     * Called once from CmsServiceProvider::boot().
     */
    public static function discover(): void
    {
        static::$plugins = [];

        // ── Plugins installed in packages/plugins/{vendor}/{name}/ ────────
        $pluginsRoot = rtrim(config('cms.packages_path', base_path('packages')), '/') . '/plugins';
        if (is_dir($pluginsRoot)) {
            foreach (glob("{$pluginsRoot}/*/*/plugin.json") ?: [] as $manifest) {
                $meta = json_decode(file_get_contents($manifest), true);
                if (empty($meta['name'])) {
                    continue;
                }
                $dir = dirname($manifest);
                static::$plugins[$meta['name']] = [
                    'source'   => 'local',
                    'path'     => $dir,
                    'assetUrl' => 'packages/plugins/' . $meta['name'],
                    'meta'     => array_merge(['removable' => true], $meta),
                ];
            }
        }

        // ── Composer package plugins in vendor/ ───────────────────────────
        $installedJson = base_path('vendor/composer/installed.json');
        if (file_exists($installedJson)) {
            $data     = json_decode(file_get_contents($installedJson), true);
            $packages = $data['packages'] ?? $data;

            foreach ($packages as $package) {
                $cms = $package['extra']['cms'] ?? null;
                if (! $cms) {
                    continue;
                }
                $types = (array) ($cms['type'] ?? []);
                if (! in_array('plugin', $types)) {
                    continue;
                }

                $name        = $package['name'];
                $installPath = base_path('vendor/' . $name);
                static::$plugins[$name] = [
                    'source'   => 'composer',
                    'path'     => $installPath,
                    'assetUrl' => 'vendor/' . $name,
                    'provider' => $cms['provider'] ?? null,
                    'meta'     => array_merge(['removable' => false], $package, [
                        'provider' => $cms['provider'] ?? null,
                    ]),
                ];
            }
        }
    }

    /** Return all discovered plugins. */
    public static function all(): array
    {
        return static::$plugins;
    }

    /** Return a single plugin by vendor/name, or null. */
    public static function get(string $name): ?array
    {
        return static::$plugins[$name] ?? null;
    }

    /**
     * Names of currently enabled plugins — read from DB.
     * Falls back to [] if the settings table is unreachable.
     */
    public static function enabledNames(): array
    {
        try {
            $raw = Setting::where('module', 'core')
                ->where('setting_key', 'enabled_plugins')
                ->value('value');

            if (! $raw) {
                return [];
            }
            $list = json_decode($raw, true);
            return is_array($list) ? array_values($list) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /** True if a plugin is enabled. */
    public static function isEnabled(string $name): bool
    {
        return in_array($name, static::enabledNames(), true);
    }

    /** Enable a plugin by vendor/name (idempotent). Also syncs any
     *  permissions and roles declared in the plugin's manifest. */
    public static function enable(string $name): void
    {
        $enabled = static::enabledNames();
        if (! in_array($name, $enabled, true)) {
            $enabled[] = $name;
        }
        static::persistEnabled($enabled);

        // Sync plugin-declared permissions and roles into the DB
        $plugin = static::get($name);
        if ($plugin) {
            static::syncPluginAccessControl($plugin);
        }
    }

    /** Disable a plugin by vendor/name (idempotent). Plugin-declared
     *  permissions and roles remain in the DB so user assignments persist
     *  across disable/enable cycles. Full cleanup happens on uninstall. */
    public static function disable(string $name): void
    {
        $enabled = array_values(array_filter(
            static::enabledNames(),
            fn ($n) => $n !== $name
        ));
        static::persistEnabled($enabled);
    }

    /**
     * Sync permissions + roles declared in a plugin's manifest into the DB.
     * Called by enable(). Also works as a standalone re-sync when the
     * manifest changes.
     *
     * Shape (in plugin.json):
     *   "permissions": { "perm.name": "description", ... }
     *   "roles":       { "role_name": { "label", "description", "permissions": [...] } }
     */
    public static function syncPluginAccessControl(array $plugin): void
    {
        $meta       = $plugin['meta'] ?? [];
        $pluginName = $meta['name']   ?? null;
        if (! $pluginName) {
            return;
        }

        // Permissions catalog — plugin permissions use the first segment
        // of the permission name as the "module" (e.g. "shop.orders.view" → module "shop")
        if (! empty($meta['permissions']) && is_array($meta['permissions'])) {
            $catalog = [];
            foreach ($meta['permissions'] as $permName => $description) {
                $module = explode('.', $permName)[0];
                $catalog[$module][$permName] = $description;
            }
            try {
                AccessControl::syncPermissions($catalog, pluginName: $pluginName);
            } catch (\Throwable) {
                // Table not ready (e.g. during install) — silently skip
            }
        }

        // Roles — plugin-declared roles are NOT is_system (can be deleted once plugin is uninstalled)
        if (! empty($meta['roles']) && is_array($meta['roles'])) {
            try {
                AccessControl::syncRoles($meta['roles'], pluginName: $pluginName, isSystem: false);
            } catch (\Throwable) {
                // Silently skip
            }
        }
    }

    /**
     * Register a plugin's service provider with the application.
     * For local plugins, registers PSR-4 autoload first (from plugin.json "autoload.psr-4").
     * Returns true if the provider was registered, false otherwise.
     */
    public static function registerProvider(array $plugin, \Illuminate\Contracts\Foundation\Application $app): bool
    {
        $provider = $plugin['meta']['provider'] ?? ($plugin['provider'] ?? null);
        if (! $provider || ! is_string($provider)) {
            return false;
        }

        // For local plugins, register PSR-4 mapping so the provider class can load
        if (($plugin['source'] ?? '') === 'local') {
            static::registerLocalAutoload($plugin);
        }

        if (! class_exists($provider)) {
            // Silently skip — bad manifest or broken plugin shouldn't crash the admin
            return false;
        }

        try {
            $app->register($provider);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** Register a plugin manually (for programmatic discovery). */
    public static function add(string $name, array $data): void
    {
        static::$plugins[$name] = $data;
    }

    // ── Internal ────────────────────────────────────────────────────────────

    protected static function persistEnabled(array $enabled): void
    {
        Setting::updateOrCreate(
            ['module' => 'core', 'setting_key' => 'enabled_plugins'],
            ['value' => json_encode(array_values(array_unique($enabled)), JSON_UNESCAPED_SLASHES)]
        );
    }

    /**
     * Register PSR-4 autoload entries for a local plugin so its classes
     * can be resolved. Reads the "autoload.psr-4" map from plugin.json.
     */
    protected static function registerLocalAutoload(array $plugin): void
    {
        $psr4 = $plugin['meta']['autoload']['psr-4'] ?? [];
        if (empty($psr4) || ! is_array($psr4)) {
            return;
        }

        $loader = static::composerLoader();
        if (! $loader) {
            return;
        }

        foreach ($psr4 as $prefix => $path) {
            $absolutePath = rtrim($plugin['path'], '/\\') . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
            $loader->addPsr4($prefix, $absolutePath);
        }
    }

    /** Grab Composer's ClassLoader instance once. */
    protected static function composerLoader(): ?ClassLoader
    {
        static $loader = null;
        if ($loader !== null) {
            return $loader;
        }
        $autoload = base_path('vendor/autoload.php');
        if (! file_exists($autoload)) {
            return null;
        }
        $loader = require $autoload;
        return $loader instanceof ClassLoader ? $loader : null;
    }
}
