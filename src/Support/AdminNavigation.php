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

/**
 * Registry of sidebar entries contributed by plugins.
 *
 * Plugins declare a `menu` block in `plugin.json`:
 *
 * ```json
 * "menu": {
 *     "placement":  "tools",    // "root" | "tools" | "none"
 *     "label":      "Social Connect",
 *     "icon":       "bi-people",
 *     "route":      "socialconnect.settings",
 *     "permission": "plugins.configure"
 * }
 * ```
 *
 * CmsServiceProvider::boot() reads this when each enabled plugin is booted
 * and calls `AdminNavigation::register()`. The admin layout reads the
 * registered entries at render time via `rootItems()` / `toolsItems()`.
 *
 * A plugin may contribute multiple entries — declare `menu` as an array of
 * objects in `plugin.json` and each one is registered independently. Big
 * plugins (shops, communities) typically want a root entry for their main UI
 * plus a Tools/Appearance entry for utility pages.
 *
 * Placement values:
 *   - `root`       — top-level link in the sidebar (alongside Dashboard, Pages, …).
 *   - `tools`      — inside the collapsible Tools dropdown.
 *   - `appearance` — inside the collapsible Appearance dropdown (alongside Themes / Menus).
 *   - `none`       — no sidebar entry (settings hub / other entry points only).
 */
class AdminNavigation
{
    /**
     * @var array<int, array{placement:string,label:string,icon?:string,route?:string,url?:string,permission?:string,plugin?:string}>
     */
    protected static array $entries = [];

    /**
     * Register a sidebar entry. Typically called from CmsServiceProvider
     * after a plugin's provider has booted and its routes are loaded.
     *
     * Silently ignores entries with placement=none (or missing placement)
     * so plugins that don't want a sidebar link can still have a menu
     * block for future flexibility.
     */
    public static function register(array $entry): void
    {
        $placement = $entry['placement'] ?? 'none';
        if (! in_array($placement, ['root', 'tools', 'appearance'], true)) {
            return;
        }
        if (empty($entry['label'])) {
            return; // label is required
        }
        static::$entries[] = $entry;
    }

    /** Entries with placement=root. */
    public static function rootItems(): array
    {
        return static::itemsFor('root');
    }

    /** Entries with placement=tools. */
    public static function toolsItems(): array
    {
        return static::itemsFor('tools');
    }

    /** Entries with placement=appearance. */
    public static function appearanceItems(): array
    {
        return static::itemsFor('appearance');
    }

    protected static function itemsFor(string $placement): array
    {
        return array_values(array_filter(
            static::$entries,
            fn ($e) => ($e['placement'] ?? null) === $placement
        ));
    }

    /** True if any tools entries are registered. */
    public static function hasTools(): bool
    {
        return count(static::toolsItems()) > 0;
    }

    /** True if any root entries are registered. */
    public static function hasRoot(): bool
    {
        return count(static::rootItems()) > 0;
    }

    /** True if any appearance entries are registered. */
    public static function hasAppearance(): bool
    {
        return count(static::appearanceItems()) > 0;
    }

    /** Reset — mainly for tests. */
    public static function clear(): void
    {
        static::$entries = [];
    }
}
