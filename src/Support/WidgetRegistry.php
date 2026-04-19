<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Support;

use Contensio\Contracts\WidgetInterface;

/**
 * Registry for widget types.
 *
 * Core and plugins register widget types here during boot.
 * The admin widget UI reads this registry to show available widgets.
 *
 * Usage — register a widget type:
 *   WidgetRegistry::register('latest-posts', \Contensio\Widgets\LatestPostsWidget::class);
 *
 * Usage — in a plugin's ServiceProvider:
 *   WidgetRegistry::register('weather', \MyPlugin\Widgets\WeatherWidget::class);
 */
class WidgetRegistry
{
    /** @var array<string, string>  type => FQCN */
    protected static array $types = [];

    /**
     * Register a widget type.
     *
     * @param string $type   Unique slug, e.g. 'latest-posts'. Must be kebab-case.
     * @param string $class  FQCN of a class implementing WidgetInterface.
     */
    public static function register(string $type, string $class): void
    {
        static::$types[$type] = $class;
    }

    /**
     * Returns all registered type slugs mapped to their class names.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return static::$types;
    }

    /**
     * Resolves and returns an instance of the widget class for $type.
     * Returns null if the type is not registered or the class doesn't exist.
     */
    public static function make(string $type): ?WidgetInterface
    {
        $class = static::$types[$type] ?? null;
        if (! $class || ! class_exists($class)) {
            return null;
        }

        $instance = app($class);
        if (! $instance instanceof WidgetInterface) {
            return null;
        }

        return $instance;
    }

    /**
     * Returns an array of resolved widget instances keyed by type slug,
     * with label, icon, description, and configSchema attached — ready
     * for the admin widget picker UI.
     */
    public static function catalog(): array
    {
        $out = [];
        foreach (static::$types as $type => $class) {
            $widget = static::make($type);
            if ($widget) {
                $out[$type] = [
                    'type'        => $type,
                    'label'       => $widget->label(),
                    'icon'        => $widget->icon(),
                    'description' => $widget->description(),
                    'schema'      => $widget->configSchema(),
                ];
            }
        }
        return $out;
    }

    /**
     * True if a widget type is registered.
     */
    public static function has(string $type): bool
    {
        return isset(static::$types[$type]);
    }

    /**
     * Wipe all registered types. Used in tests.
     */
    public static function clearAll(): void
    {
        static::$types = [];
    }
}
