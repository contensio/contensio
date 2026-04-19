<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Support;

use Contensio\Models\WidgetInstance;

/**
 * Widget area registry and renderer.
 *
 * Themes declare their widget areas in theme.json under "widget_areas".
 * Templates render an area by calling WidgetArea::render('area-id').
 *
 * Usage in a Blade template:
 *   {!! \Contensio\Support\WidgetArea::render('after-post') !!}
 *
 * The call is safe even if no widgets are placed — returns '' when empty.
 */
class WidgetArea
{
    /** @var array<string, array{id: string, label: string}>  Registered areas. */
    protected static array $areas = [];

    /**
     * Register a widget area. Called during theme boot from theme.json.
     */
    public static function register(string $id, string $label): void
    {
        static::$areas[$id] = ['id' => $id, 'label' => $label];
    }

    /**
     * Register multiple areas at once from a theme.json widget_areas array.
     *
     * Expected format:
     *   [{ "id": "sidebar", "label": "Sidebar" }, ...]
     */
    public static function registerMany(array $areas): void
    {
        foreach ($areas as $area) {
            if (! empty($area['id']) && ! empty($area['label'])) {
                static::register($area['id'], $area['label']);
            }
        }
    }

    /**
     * Returns all registered areas.
     *
     * @return array<string, array{id: string, label: string}>
     */
    public static function all(): array
    {
        return static::$areas;
    }

    /**
     * Render all active widget instances in $areaId, concatenated in position order.
     * Returns '' when the area has no active widgets or the DB isn't ready.
     */
    public static function render(string $areaId): string
    {
        try {
            $instances = WidgetInstance::where('area_id', $areaId)
                ->where('is_active', true)
                ->orderBy('position')
                ->get();
        } catch (\Throwable) {
            return '';
        }

        if ($instances->isEmpty()) {
            return '';
        }

        $output = '';
        foreach ($instances as $instance) {
            $widget = WidgetRegistry::make($instance->widget_type);
            if (! $widget) {
                continue; // widget type was unregistered (plugin disabled)
            }

            // Merge stored config with schema defaults so render() always
            // receives a complete config array even for newly added fields.
            $schema   = $widget->configSchema();
            $defaults = array_map(fn ($field) => $field['default'] ?? null, $schema);
            $config   = array_merge($defaults, $instance->config ?? []);

            try {
                $html = $widget->render($config);
                if ($html) {
                    $output .= '<div class="contensio-widget contensio-widget--' . e($instance->widget_type) . '">' . $html . '</div>';
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $output;
    }

    /**
     * Wipe all registered areas. Used in tests.
     */
    public static function clearAll(): void
    {
        static::$areas = [];
    }
}
