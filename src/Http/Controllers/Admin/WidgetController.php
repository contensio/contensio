<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Http\Controllers\Admin;

use Contensio\Models\WidgetInstance;
use Contensio\Support\WidgetArea;
use Contensio\Support\WidgetRegistry;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WidgetController extends Controller
{
    public function index(Request $request)
    {
        $areas    = WidgetArea::all();
        $catalog  = WidgetRegistry::catalog();
        $activeArea = $request->get('area', array_key_first($areas) ?? 'sidebar');

        $instances = WidgetInstance::where('area_id', $activeArea)
            ->orderBy('position')
            ->get()
            ->map(function ($instance) use ($catalog) {
                $schema   = $catalog[$instance->widget_type]['schema'] ?? [];
                $defaults = array_map(fn ($f) => $f['default'] ?? null, $schema);
                $instance->merged_config = array_merge($defaults, $instance->config ?? []);
                $instance->widget_label  = $catalog[$instance->widget_type]['label'] ?? $instance->widget_type;
                $instance->widget_icon   = $catalog[$instance->widget_type]['icon']  ?? 'bi-puzzle';
                return $instance;
            });

        return view('contensio::admin.widgets.index', compact('areas', 'catalog', 'instances', 'activeArea'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id'     => 'required|string|max:80',
            'widget_type' => 'required|string|max:80',
        ]);

        $areaId     = $request->input('area_id');
        $widgetType = $request->input('widget_type');

        if (! WidgetRegistry::has($widgetType)) {
            return back()->withErrors(['widget_type' => 'Unknown widget type.']);
        }

        // Place at the end of the area
        $maxPosition = WidgetInstance::where('area_id', $areaId)->max('position') ?? -1;

        // Build default config from schema
        $widget   = WidgetRegistry::make($widgetType);
        $defaults = array_map(fn ($f) => $f['default'] ?? null, $widget->configSchema());

        WidgetInstance::create([
            'area_id'     => $areaId,
            'widget_type' => $widgetType,
            'position'    => $maxPosition + 1,
            'config'      => $defaults,
            'is_active'   => true,
        ]);

        return redirect()->route('contensio.widgets', ['area' => $areaId])
            ->with('success', 'Widget added.');
    }

    public function update(Request $request, WidgetInstance $widget)
    {
        $schema = WidgetRegistry::make($widget->widget_type)?->configSchema() ?? [];

        $config = [];
        foreach ($schema as $key => $field) {
            $value = $request->input('config.' . $key);

            $config[$key] = match ($field['type']) {
                'checkbox' => (bool) $value,
                'number'   => is_numeric($value) ? (int) $value : ($field['default'] ?? 0),
                default    => $value ?? $field['default'] ?? '',
            };
        }

        $widget->update(['config' => $config]);

        return redirect()->route('contensio.widgets', ['area' => $widget->area_id])
            ->with('success', 'Widget updated.');
    }

    public function toggle(WidgetInstance $widget)
    {
        $widget->update(['is_active' => ! $widget->is_active]);
        return redirect()->route('contensio.widgets', ['area' => $widget->area_id]);
    }

    public function moveUp(WidgetInstance $widget)
    {
        $prev = WidgetInstance::where('area_id', $widget->area_id)
            ->where('position', '<', $widget->position)
            ->orderByDesc('position')
            ->first();

        if ($prev) {
            [$widget->position, $prev->position] = [$prev->position, $widget->position];
            $widget->save();
            $prev->save();
        }

        return redirect()->route('contensio.widgets', ['area' => $widget->area_id]);
    }

    public function moveDown(WidgetInstance $widget)
    {
        $next = WidgetInstance::where('area_id', $widget->area_id)
            ->where('position', '>', $widget->position)
            ->orderBy('position')
            ->first();

        if ($next) {
            [$widget->position, $next->position] = [$next->position, $widget->position];
            $widget->save();
            $next->save();
        }

        return redirect()->route('contensio.widgets', ['area' => $widget->area_id]);
    }

    public function destroy(WidgetInstance $widget)
    {
        $areaId = $widget->area_id;
        $widget->delete();

        // Re-normalise positions
        WidgetInstance::where('area_id', $areaId)
            ->orderBy('position')
            ->get()
            ->each(function ($w, $i) { $w->update(['position' => $i]); });

        return redirect()->route('contensio.widgets', ['area' => $areaId])
            ->with('success', 'Widget removed.');
    }
}
