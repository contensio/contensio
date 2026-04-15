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
 * ## Core hook points (by convention — more can be added as needed)
 *
 * - `login.after_form`   — below the sign-in form on the login page
 * - `profile.sections`   — bottom of the /admin/profile page (receives $user)
 * - `settings.hub_cards` — inside the grid on /admin/settings
 * - `admin.sidebar.*`    — various sidebar regions (future)
 *
 * ## Usage in core Blade views
 *
 * ```blade
 * {!! \Contensio\Cms\Support\Hook::render('login.after_form') !!}
 * {!! \Contensio\Cms\Support\Hook::render('profile.sections', $user) !!}
 * ```
 *
 * ## Usage in a plugin's service provider
 *
 * ```php
 * use Contensio\Cms\Support\Hook;
 *
 * public function boot(): void {
 *     Hook::add('login.after_form', function () {
 *         return view('my-plugin::login-buttons')->render();
 *     });
 * }
 * ```
 */
class Hook
{
    /**
     * @var array<string, array<int, array<int, callable>>>  hookName => priority => [callbacks]
     */
    protected static array $callbacks = [];

    /**
     * Register a callback under a hook name. Lower priority numbers run first.
     * Same-priority callbacks run in registration order.
     */
    public static function add(string $name, callable $callback, int $priority = 10): void
    {
        static::$callbacks[$name][$priority][] = $callback;
    }

    /**
     * Render every callback registered under $name, concatenating their string output.
     * Any extra arguments are passed through to each callback.
     *
     * Returns an empty string if no callbacks are registered — safe to use
     * unconditionally in templates.
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
                    // One broken plugin shouldn't take down the whole page.
                    // Log the error but continue rendering other hooks.
                    if (function_exists('report')) {
                        report($e);
                    }
                }
            }
        }
        return $output;
    }

    /**
     * True if at least one callback is registered under $name.
     * Useful for conditional wrappers: `@if(Hook::has('login.after_form')) <div>...</div> @endif`
     */
    public static function has(string $name): bool
    {
        return ! empty(static::$callbacks[$name]);
    }

    /**
     * Remove all callbacks registered under a name. Mainly for tests.
     */
    public static function clear(string $name): void
    {
        unset(static::$callbacks[$name]);
    }

    /**
     * Wipe every registered callback. Mainly for tests.
     */
    public static function clearAll(): void
    {
        static::$callbacks = [];
    }
}
