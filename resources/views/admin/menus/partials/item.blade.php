{{--
 | Contensio - The open content platform for Laravel.
 | Admin — menus partials item.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

{{--
    Renders a single menu item row in the editor.
    Variables:
        $item          — MenuItem model with translations
        $index         — position in the list (for default position value)
        $languages     — collection of Language models
        $defaultLangId — id of the default language
        $parentOptions — collection of top-level items to pick from as parent
--}}

@php
    $typeLabel = match($item->type) {
        'page'       => 'Page',
        'post'       => 'Post',
        'term'       => 'Term',
        'content'    => 'Content',
        'custom_url' => 'Link',
        default      => $item->type,
    };
    $isNested  = ! is_null($item->parent_id);
    $fieldBase = "items[{$item->id}]";

    // Seed per-language label + url maps for Alpine so the header and inputs
    // stay in sync as the user types and switches language tabs.
    $labelsJs = $languages->mapWithKeys(function ($lang) use ($item) {
        $tr = $item->translations->firstWhere('language_id', $lang->id);
        return [$lang->id => $tr?->label ?? ''];
    })->toArray();
    $urlsJs = $languages->mapWithKeys(function ($lang) use ($item) {
        $tr = $item->translations->firstWhere('language_id', $lang->id);
        return [$lang->id => $tr?->url ?? ''];
    })->toArray();
@endphp

<div id="menu-item-{{ $item->id }}"
     data-item-id="{{ $item->id }}"
     class="border border-gray-200 rounded-lg {{ $isNested ? 'ml-6 border-l-4 border-l-blue-200' : '' }}"
     x-data="{
         open: false,
         labels: @js($labelsJs),
         urls:   @js($urlsJs)
     }">

    {{-- Hidden identifier --}}
    <input type="hidden" form="menu-form" name="{{ $fieldBase }}[id]" value="{{ $item->id }}">
    <input type="hidden" form="menu-form" name="{{ $fieldBase }}[type]" value="{{ $item->type }}">
    <input type="hidden" form="menu-form" name="{{ $fieldBase }}[reference_id]" value="{{ $item->reference_id }}">
    <input type="hidden" form="menu-form" name="{{ $fieldBase }}[is_active]" value="1">

    {{-- Collapsed header --}}
    <div class="flex items-center gap-3 px-4 py-3 bg-white">
        {{-- Drag handle --}}
        <button type="button"
                class="menu-item-handle shrink-0 p-1.5 -ml-1.5 text-gray-300 hover:text-gray-600
                       cursor-grab active:cursor-grabbing rounded-md hover:bg-gray-50 transition-colors"
                title="Drag to reorder">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16">
                <path d="M7 2a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
            </svg>
        </button>

        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold
                     bg-gray-100 text-gray-600 uppercase tracking-wider shrink-0">
            {{ $typeLabel }}
        </span>

        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate"
               x-text="labels[activeLang] || labels[{{ $defaultLangId }}] || '—'"></p>
            @if($item->type === 'custom_url')
            <p class="text-xs text-gray-400 truncate font-mono"
               x-text="urls[activeLang] || urls[{{ $defaultLangId }}] || ''"></p>
            @endif
        </div>

        {{-- Toggle --}}
        <button type="button"
                @click="open = !open"
                class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors">
            <svg class="w-4 h-4 transition-transform"
                 :class="open ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        {{-- Delete: removes the row from the DOM. The item is then excluded from the form
             submission, so the controller's whereNotIn cleanup deletes it server-side on Save. --}}
        <button type="button"
                @click="$dispatch('cms:confirm', {
                    title: 'Remove menu item',
                    description: 'Remove this item from the menu? You\'ll still need to click Save Menu to persist the change.',
                    confirmLabel: 'Remove',
                    removeSelector: '#menu-item-{{ $item->id }}'
                })"
                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"
                title="Remove">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>
    </div>

    {{-- Expanded fields --}}
    <div x-show="open" x-cloak class="border-t border-gray-100 bg-gray-50/50">
        <div class="p-4 space-y-3">

            {{-- Labels per language --}}
            @foreach($languages as $lang)
            @php $tr = $item->translations->firstWhere('language_id', $lang->id); @endphp
            <div x-show="activeLang === {{ $lang->id }}">
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Label <span class="text-gray-400 font-normal">({{ $lang->name }})</span>
                </label>
                <input type="text"
                       form="menu-form"
                       name="{{ $fieldBase }}[translations][{{ $lang->id }}][label]"
                       x-model="labels[{{ $lang->id }}]"
                       class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm
                              focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">

                @if($item->type === 'custom_url')
                <label class="block text-xs font-medium text-gray-600 mb-1 mt-2">
                    URL <span class="text-gray-400 font-normal">({{ $lang->name }})</span>
                </label>
                <input type="text"
                       form="menu-form"
                       name="{{ $fieldBase }}[translations][{{ $lang->id }}][url]"
                       x-model="urls[{{ $lang->id }}]"
                       placeholder="https://..."
                       class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                @endif
            </div>
            @endforeach

            <div class="grid grid-cols-2 gap-3">
                {{-- Target --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Open link in</label>
                    <select form="menu-form"
                            name="{{ $fieldBase }}[target]"
                            class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm bg-white
                                   focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                        <option value="_self"  {{ $item->target === '_self' ? 'selected' : '' }}>Same tab</option>
                        <option value="_blank" {{ $item->target === '_blank' ? 'selected' : '' }}>New tab</option>
                    </select>
                </div>

                {{-- Parent --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Parent item</label>
                    <select form="menu-form"
                            name="{{ $fieldBase }}[parent_id]"
                            class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm bg-white
                                   focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                        <option value="">— Top level —</option>
                        @foreach($parentOptions as $opt)
                            @if($opt['id'] !== $item->id)
                            <option value="{{ $opt['id'] }}" {{ (int) $item->parent_id === $opt['id'] ? 'selected' : '' }}>
                                {{ $opt['label'] }}
                            </option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
    </div>
</div>
