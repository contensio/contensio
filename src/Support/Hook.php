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

/**
 * Extension point registry — the way plugins inject content into core views.
 *
 * Core templates call `Hook::render('name')` at well-known locations. Plugins
 * register callbacks with `Hook::add('name', fn() => '<html>...')` from their
 * service provider. Each callback returns a string; the render method
 * concatenates all registered callbacks' output in priority order.
 *
 * This is Contensio's equivalent of WordPress's `do_action()` / `apply_filters()`
 * or Drupal's hook system — a lightweight, purpose-built mechanism so plugins
 * can extend UI without modifying core files.
 *
 * ## Core render hook points
 *
 * ### Login / auth
 * - `contensio/admin/login-after-form`     — below the sign-in form
 *
 * ### Dashboard  (/account)
 * - `contensio/admin/dashboard-quick-actions` — extra buttons in the header action bar (right of "Upload media")
 * - `contensio/admin/dashboard-stats`         — below the built-in stat cards row; render your own stat row here
 * - `contensio/admin/dashboard-widgets`       — between the content panels and the activity log; full-width panel(s)
 * - `contensio/admin/dashboard-after`         — below the activity log; lowest-priority content
 *
 * ### Profile  (/account/profile)
 * - `contensio/admin/profile-sections`    — bottom of the profile page (receives $user)
 *
 * ### Settings  (/account/settings)
 * - `contensio/admin/settings-cards`      — extra cards inside the settings hub grid
 *
 * ## Usage in core Blade views
 *
 * ```blade
 * {!! \Contensio\Support\Hook::render('contensio/admin/dashboard-stats') !!}
 * {!! \Contensio\Support\Hook::render('contensio/admin/profile-sections', $user) !!}
 * ```
 *
 * ## Usage in a plugin's service provider
 *
 * ```php
 * use Contensio\Support\Hook;
 *
 * public function boot(): void
 * {
 *     // Add stat cards to the dashboard stats row
 *     Hook::add('contensio/admin/dashboard-stats', function () {
 *         $orders  = \MyPlugin\Models\Order::count();
 *         $revenue = \MyPlugin\Models\Order::sum('total');
 *         return view('my-plugin::dashboard.stats', compact('orders', 'revenue'))->render();
 *     });
 *
 *     // Add a "Recent orders" panel to the dashboard widget area
 *     Hook::add('contensio/admin/dashboard-widgets', function () {
 *         $recent = \MyPlugin\Models\Order::latest()->limit(5)->get();
 *         return view('my-plugin::dashboard.recent-orders', compact('recent'))->render();
 *     });
 *
 *     // Add a quick-action button to the dashboard header
 *     Hook::add('contensio/admin/dashboard-quick-actions', function () {
 *         return '<a href="/account/shop/orders" class="inline-flex items-center gap-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-3 py-2 rounded-lg transition-colors">Orders</a>';
 *     });
 * }
 * ```
 */
class Hook
{
    // ── UI render hooks (original system — plugins inject HTML strings) ────────

    /**
     * @var array<string, array<int, array<int, callable>>>  hookName => priority => [callbacks]
     */
    protected static array $callbacks = [];

    /**
     * Register a UI render callback. The callback must return a string.
     * Lower priority numbers run first.
     */
    public static function add(string $name, callable $callback, int $priority = 10): void
    {
        static::$callbacks[$name][$priority][] = $callback;
    }

    /**
     * Run every render callback under $name in priority order, concatenating
     * their string output. Extra arguments are passed through to each callback.
     * Returns '' if nothing is registered — safe to use unconditionally in templates.
     */
    public static function render(string $name, mixed ...$args): string
    {
        if (empty(static::$callbacks[$name])) {
            return '';
        }

        $buckets = static::$callbacks[$name];
        ksort($buckets);

        $output = '';
        foreach ($buckets as $callbacks) {
            foreach ($callbacks as $cb) {
                try {
                    $result = $cb(...$args);
                    if (is_string($result)) {
                        $output .= $result;
                    }
                } catch (\Throwable $e) {
                    if (function_exists('report')) {
                        report($e);
                    }
                }
            }
        }
        return $output;
    }

    /**
     * True if at least one render callback is registered under $name.
     */
    public static function has(string $name): bool
    {
        return ! empty(static::$callbacks[$name]);
    }

    /**
     * Remove all render callbacks under $name. Mainly for tests.
     */
    public static function clear(string $name): void
    {
        unset(static::$callbacks[$name]);
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * @var array<string, array<int, array<int, callable>>>
     */
    protected static array $actions = [];

    /**
     * Register an action callback. Callbacks are called in priority order;
     * lower numbers run first. Return values are ignored.
     */
    public static function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        static::$actions[$hook][$priority][] = $callback;
    }

    /**
     * Fire all callbacks registered for $hook, passing $args to each.
     * Errors in individual callbacks are reported but do not stop execution.
     */
    public static function doAction(string $hook, mixed ...$args): void
    {
        if (empty(static::$actions[$hook])) {
            return;
        }

        $buckets = static::$actions[$hook];
        ksort($buckets);

        foreach ($buckets as $callbacks) {
            foreach ($callbacks as $cb) {
                try {
                    $cb(...$args);
                } catch (\Throwable $e) {
                    if (function_exists('report')) {
                        report($e);
                    }
                }
            }
        }
    }

    /**
     * Remove a specific action callback (must be the same callable reference).
     */
    public static function removeAction(string $hook, callable $callback, int $priority = 10): void
    {
        if (empty(static::$actions[$hook][$priority])) {
            return;
        }
        static::$actions[$hook][$priority] = array_values(
            array_filter(static::$actions[$hook][$priority], fn ($cb) => $cb !== $callback)
        );
    }

    /**
     * True if at least one action callback is registered under $hook.
     */
    public static function hasAction(string $hook): bool
    {
        return ! empty(static::$actions[$hook]);
    }

    // ── Filters ───────────────────────────────────────────────────────────────

    /**
     * @var array<string, array<int, array<int, callable>>>
     */
    protected static array $filters = [];

    /**
     * Register a filter callback. Each callback receives the current value as
     * its first argument (plus any extra args), must modify it, and return it.
     */
    public static function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        static::$filters[$hook][$priority][] = $callback;
    }

    /**
     * Pass $value through every callback registered for $hook in priority order.
     * Each callback's return value becomes the input for the next.
     * If a callback throws, the error is reported and the value passes through unchanged.
     * Returns the final filtered value.
     */
    public static function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (empty(static::$filters[$hook])) {
            return $value;
        }

        $buckets = static::$filters[$hook];
        ksort($buckets);

        foreach ($buckets as $callbacks) {
            foreach ($callbacks as $cb) {
                try {
                    $value = $cb($value, ...$args);
                } catch (\Throwable $e) {
                    if (function_exists('report')) {
                        report($e);
                    }
                }
            }
        }

        return $value;
    }

    /**
     * Remove a specific filter callback (must be the same callable reference).
     */
    public static function removeFilter(string $hook, callable $callback, int $priority = 10): void
    {
        if (empty(static::$filters[$hook][$priority])) {
            return;
        }
        static::$filters[$hook][$priority] = array_values(
            array_filter(static::$filters[$hook][$priority], fn ($cb) => $cb !== $callback)
        );
    }

    /**
     * True if at least one filter callback is registered under $hook.
     */
    public static function hasFilter(string $hook): bool
    {
        return ! empty(static::$filters[$hook]);
    }

    // ── Housekeeping ──────────────────────────────────────────────────────────

    /**
     * Wipe every registered callback (render + actions + filters). Mainly for tests.
     */
    public static function clearAll(): void
    {
        static::$callbacks = [];
        static::$actions   = [];
        static::$filters   = [];
    }
}
