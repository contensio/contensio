@extends('contensio::admin.layout')

@section('title', 'Widgets')

@section('content')
<div class="p-6" x-data="{ configOpen: null }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Widgets</h1>
            <p class="mt-1 text-gray-500">Place widgets into your theme's areas. Changes take effect immediately.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif

    @if(empty($areas))
    <div class="rounded-xl border border-dashed border-gray-200 p-12 text-center text-gray-400">
        <i class="bi bi-layout-sidebar text-4xl mb-3 block"></i>
        <p class="font-medium text-gray-600">No widget areas registered</p>
        <p class="text-sm mt-1">The active theme doesn't declare any widget areas. Check the theme's <code>theme.json</code>.</p>
    </div>
    @else

    <div class="flex gap-6 items-start">

        {{-- Left: area tabs + instances --}}
        <div class="flex-1 min-w-0">

            {{-- Area tab bar --}}
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach($areas as $area)
                <a href="{{ route('contensio.widgets', ['area' => $area['id']]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ $activeArea === $area['id'] ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-300' }}">
                    {{ $area['label'] }}
                </a>
                @endforeach
            </div>

            {{-- Current area instances --}}
            @if($instances->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-200 p-10 text-center text-gray-400">
                <i class="bi bi-layout-text-sidebar text-3xl mb-2 block"></i>
                <p class="text-sm">No widgets in this area yet. Add one from the panel on the right.</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($instances as $instance)
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

                    {{-- Instance header --}}
                    <div class="flex items-center gap-3 px-4 py-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                            <i class="bi {{ $instance->widget_icon }} text-gray-500"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 text-sm">{{ $instance->widget_label }}</p>
                            @if(! empty($instance->merged_config['title']))
                            <p class="text-xs text-gray-400 truncate">"{{ $instance->merged_config['title'] }}"</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-1">
                            {{-- Move up --}}
                            <form method="POST" action="{{ route('contensio.widgets.moveUp', $instance) }}">
                                @csrf @method('PATCH')
                                <button type="submit" title="Move up"
                                    class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                                    <i class="bi bi-chevron-up text-xs"></i>
                                </button>
                            </form>
                            {{-- Move down --}}
                            <form method="POST" action="{{ route('contensio.widgets.moveDown', $instance) }}">
                                @csrf @method('PATCH')
                                <button type="submit" title="Move down"
                                    class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                                    <i class="bi bi-chevron-down text-xs"></i>
                                </button>
                            </form>
                            {{-- Toggle active --}}
                            <form method="POST" action="{{ route('contensio.widgets.toggle', $instance) }}">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $instance->is_active ? 'Disable' : 'Enable' }}"
                                    class="w-7 h-7 flex items-center justify-center rounded transition-colors
                                           {{ $instance->is_active ? 'text-green-600 hover:bg-green-50' : 'text-gray-300 hover:bg-gray-100' }}">
                                    <i class="bi {{ $instance->is_active ? 'bi-eye' : 'bi-eye-slash' }}"></i>
                                </button>
                            </form>
                            {{-- Configure toggle --}}
                            <button type="button"
                                @click="configOpen = (configOpen === {{ $instance->id }}) ? null : {{ $instance->id }}"
                                title="Configure"
                                class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                                <i class="bi bi-gear"></i>
                            </button>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('contensio.widgets.destroy', $instance) }}"
                                  onsubmit="return confirm('Remove this widget?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Remove"
                                    class="w-7 h-7 flex items-center justify-center rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Inline config panel --}}
                    @php $schema = $catalog[$instance->widget_type]['schema'] ?? []; @endphp
                    @if(! empty($schema))
                    <div x-show="configOpen === {{ $instance->id }}" x-cloak
                         class="border-t border-gray-100 px-4 py-4 bg-gray-50">
                        <form method="POST" action="{{ route('contensio.widgets.update', $instance) }}" class="space-y-4">
                            @csrf @method('PATCH')
                            @foreach($schema as $key => $field)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field['label'] }}</label>
                                @if($field['type'] === 'checkbox')
                                <input type="checkbox" name="config[{{ $key }}]" value="1"
                                    class="w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-400"
                                    {{ ! empty($instance->merged_config[$key]) ? 'checked' : '' }}>
                                @elseif($field['type'] === 'textarea')
                                <textarea name="config[{{ $key }}]" rows="{{ $field['rows'] ?? 4 }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-400 {{ ! empty($field['monospace']) ? 'font-mono' : '' }}">{{ $instance->merged_config[$key] ?? '' }}</textarea>
                                @elseif($field['type'] === 'select')
                                <select name="config[{{ $key }}]"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-400">
                                    @foreach($field['options'] ?? [] as $val => $lbl)
                                    <option value="{{ $val }}" {{ ($instance->merged_config[$key] ?? '') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                @elseif($field['type'] === 'number')
                                <input type="number" name="config[{{ $key }}]"
                                    value="{{ $instance->merged_config[$key] ?? $field['default'] ?? '' }}"
                                    min="{{ $field['min'] ?? '' }}" max="{{ $field['max'] ?? '' }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-400">
                                @else
                                <input type="text" name="config[{{ $key }}]"
                                    value="{{ $instance->merged_config[$key] ?? $field['default'] ?? '' }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-400">
                                @endif
                            </div>
                            @endforeach
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="bg-ember-500 hover:bg-ember-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Right: available widget types --}}
        <div class="w-72 shrink-0">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden sticky top-6">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900">Available Widgets</p>
                    <p class="text-xs text-gray-400 mt-0.5">Click to add to <strong>{{ $areas[$activeArea]['label'] ?? $activeArea }}</strong></p>
                </div>
                <div class="divide-y divide-gray-50 max-h-[600px] overflow-y-auto">
                    @foreach($catalog as $type => $widget)
                    <form method="POST" action="{{ route('contensio.widgets.store') }}">
                        @csrf
                        <input type="hidden" name="area_id"     value="{{ $activeArea }}">
                        <input type="hidden" name="widget_type" value="{{ $type }}">
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 group-hover:bg-ember-50 flex items-center justify-center shrink-0 transition-colors">
                                <i class="bi {{ $widget['icon'] }} text-gray-500 group-hover:text-ember-600 transition-colors"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $widget['label'] }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $widget['description'] }}</p>
                            </div>
                            <i class="bi bi-plus text-gray-300 group-hover:text-ember-500 ml-auto shrink-0 transition-colors"></i>
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
    @endif
</div>
@endsection
