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
 * Per-plugin configuration values.
 *
 * Schema source:   plugin.json → "settings.sections[].fields[]"
 *                  (or composer.json → "extra.cms.settings" for Composer plugins)
 * Value storage:   settings table — one row per plugin
 *                  module      = "plugin_options"
 *                  setting_key = vendor/name
 *                  value       = JSON blob of all values
 *
 * Mirror of ThemeOptions. Same field types, same storage pattern, same API
 * shape — so plugin authors and theme authors work from a single mental model.
 */
class PluginOptions
{
    /** Per-request memoization. */
    protected static array $cache = [];

    /**
     * All values for a plugin, with defaults applied.
     */
    public static function all(string $plugin): array
    {
        if (isset(static::$cache[$plugin])) {
            return static::$cache[$plugin];
        }

        $defaults = static::defaults($plugin);
        $saved    = [];

        try {
            $raw = Setting::where('module', 'plugin_options')
                ->where('setting_key', $plugin)
                ->value('value');
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $saved = $decoded;
                }
            }
        } catch (\Throwable) {
            // Settings table missing or DB unreachable — fall back to defaults
        }

        return static::$cache[$plugin] = array_merge($defaults, $saved);
    }

    /**
     * Look up a single option value. Falls back to $default if the key
     * isn't defined in the schema and hasn't been saved.
     */
    public static function get(string $plugin, string $key, mixed $default = null): mixed
    {
        $all = static::all($plugin);
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    /**
     * The schema ("sections" array) declared by a plugin in its manifest.
     */
    public static function schema(string $plugin): array
    {
        $data = PluginRegistry::get($plugin);
        return $data['meta']['settings']['sections'] ?? [];
    }

    /**
     * True if the plugin declares any settings.
     */
    public static function hasSchema(string $plugin): bool
    {
        return ! empty(static::schema($plugin));
    }

    /**
     * Flat array of defaults extracted from the schema.
     */
    public static function defaults(string $plugin): array
    {
        $defaults = [];
        foreach (static::schema($plugin) as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                if (empty($field['key'])) {
                    continue;
                }
                $defaults[$field['key']] = $field['default'] ?? null;
            }
        }
        return $defaults;
    }

    /**
     * Persist values for a plugin. Preserves keys that are already stored
     * but no longer in the schema (forward/backward compatibility).
     */
    public static function save(string $plugin, array $values): void
    {
        $existing = static::all($plugin);
        $merged   = array_merge($existing, $values);

        Setting::updateOrCreate(
            ['module' => 'plugin_options', 'setting_key' => $plugin],
            ['value' => json_encode($merged, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]
        );

        unset(static::$cache[$plugin]);
    }

    /** Reset a plugin to declared defaults (delete the saved row). */
    public static function reset(string $plugin): void
    {
        Setting::where('module', 'plugin_options')
            ->where('setting_key', $plugin)
            ->delete();
        unset(static::$cache[$plugin]);
    }
}
