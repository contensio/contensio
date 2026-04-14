{{--
 | Contensio - The open content platform for Laravel.
 | Admin — themes partials field.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

{{--
    Renders a single theme customization field.
    Variables:
        $field — array ['key','type','label','default','help', ...type-specific keys]
        $value — current saved value (or default)

    All inputs post as options[<key>] so the controller walks the schema and
    ignores anything not declared.
--}}

@php
    $key   = $field['key']   ?? '';
    $type  = $field['type']  ?? 'text';
    $label = $field['label'] ?? $key;
    $help  = $field['help']  ?? null;
    $name  = "options[{$key}]";
    $id    = "opt_{$key}";
@endphp

<div class="space-y-1.5">
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>

    @switch($type)

        {{-- ── Text ─────────────────────────────────────────────────────── --}}
        @case('text')
        <input type="text"
               id="{{ $id }}"
               name="{{ $name }}"
               value="{{ $value }}"
               placeholder="{{ $field['placeholder'] ?? '' }}"
               maxlength="{{ $field['maxlength'] ?? 255 }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        @break

        {{-- ── Textarea ─────────────────────────────────────────────────── --}}
        @case('textarea')
        <textarea id="{{ $id }}"
                  name="{{ $name }}"
                  rows="{{ $field['rows'] ?? 4 }}"
                  placeholder="{{ $field['placeholder'] ?? '' }}"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm resize-y
                         focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                         {{ ! empty($field['monospace']) ? 'font-mono text-xs' : '' }}">{{ $value }}</textarea>
        @break

        {{-- ── Number ───────────────────────────────────────────────────── --}}
        @case('number')
        <input type="number"
               id="{{ $id }}"
               name="{{ $name }}"
               value="{{ $value }}"
               @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
               @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
               @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
               class="w-full sm:w-48 rounded-lg border border-gray-300 px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        @break

        {{-- ── Range ────────────────────────────────────────────────────── --}}
        @case('range')
        @php
            $min  = $field['min']  ?? 0;
            $max  = $field['max']  ?? 100;
            $step = $field['step'] ?? 1;
            $unit = $field['unit'] ?? '';
        @endphp
        <div class="flex items-center gap-3" x-data="{ val: @js((int) $value) }">
            <input type="range"
                   id="{{ $id }}"
                   name="{{ $name }}"
                   x-model="val"
                   min="{{ $min }}"
                   max="{{ $max }}"
                   step="{{ $step }}"
                   class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
            <div class="w-20 text-sm font-mono text-gray-700 text-center bg-gray-100 border border-gray-200 rounded-md px-2 py-1">
                <span x-text="val"></span><span class="text-gray-400">{{ $unit }}</span>
            </div>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1">
            <span>{{ $min }}{{ $unit }}</span>
            <span>{{ $max }}{{ $unit }}</span>
        </div>
        @break

        {{-- ── Color ────────────────────────────────────────────────────── --}}
        @case('color')
        <div class="flex items-center gap-3" x-data="{ val: @js((string) $value) }">
            <div class="relative">
                <input type="color"
                       x-model="val"
                       class="w-10 h-10 rounded-lg border border-gray-300 cursor-pointer appearance-none p-0">
            </div>
            <input type="text"
                   name="{{ $name }}"
                   x-model="val"
                   pattern="#[0-9a-fA-F]{3,8}"
                   class="w-32 font-mono text-sm rounded-lg border border-gray-300 px-3 py-2
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <div class="flex-1"></div>
        </div>
        @break

        {{-- ── Select ───────────────────────────────────────────────────── --}}
        @case('select')
        <select id="{{ $id }}"
                name="{{ $name }}"
                class="w-full sm:w-80 rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            @foreach(($field['options'] ?? []) as $optKey => $optLabel)
            <option value="{{ $optKey }}" {{ (string) $value === (string) $optKey ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
            @endforeach
        </select>
        @break

        {{-- ── Radio ────────────────────────────────────────────────────── --}}
        @case('radio')
        <div class="space-y-2">
            @foreach(($field['options'] ?? []) as $optKey => $optLabel)
            <label class="flex items-center gap-2.5 cursor-pointer group">
                <input type="radio"
                       name="{{ $name }}"
                       value="{{ $optKey }}"
                       {{ (string) $value === (string) $optKey ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $optLabel }}</span>
            </label>
            @endforeach
        </div>
        @break

        {{-- ── Checkbox ─────────────────────────────────────────────────── --}}
        @case('checkbox')
        <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox"
                   id="{{ $id }}"
                   name="{{ $name }}"
                   value="1"
                   {{ $value ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <span class="text-sm text-gray-700">{{ $field['checkbox_label'] ?? 'Enabled' }}</span>
        </label>
        @break

        {{-- ── Image ────────────────────────────────────────────────────── --}}
        @case('image')
        <div x-data="{ url: @js((string) $value) }" class="space-y-2">
            <input type="text"
                   name="{{ $name }}"
                   x-model="url"
                   placeholder="https://..."
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <template x-if="url">
                <img :src="url" alt="" class="max-h-32 rounded-lg border border-gray-200">
            </template>
        </div>
        @break

        {{-- ── Fallback ─────────────────────────────────────────────────── --}}
        @default
        <input type="text"
               id="{{ $id }}"
               name="{{ $name }}"
               value="{{ $value }}"
               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

    @endswitch

    @if($help)
    <p class="text-xs text-gray-500 leading-relaxed">{{ $help }}</p>
    @endif
</div>
