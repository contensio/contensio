{{--
 | Contensio - The open content platform for Laravel.
 | Admin — Contact page builder + settings (tabbed).
 | https://contensio.com
 |
 | @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license     AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}
@extends('contensio::admin.layout')

@section('title', __('contensio::admin.contact.title'))

@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('contensio::admin.contact.title') }}</span>
@endsection

@push('styles')
<style>
[x-cloak] { display: none !important; }
.tiptap-toolbar { border:1px solid #d1d5db; border-bottom:none; border-radius:.5rem .5rem 0 0; background:#f9fafb; padding:.375rem .5rem; display:flex; align-items:center; flex-wrap:wrap; gap:3px; }
.tiptap-toolbar button { padding:.4rem .6rem; border-radius:.375rem; color:#374151; font-size:1rem; font-weight:600; cursor:pointer; background:transparent; border:none; line-height:1; min-width:2rem; text-align:center; }
.tiptap-toolbar button:hover, .tiptap-toolbar button.active { background:#e5e7eb; }
.tiptap-toolbar .sep { display:inline-block; width:1px; align-self:stretch; background:#d1d5db; margin:.2rem .1rem; }
.tiptap-editor { min-height:160px; border:1px solid #d1d5db; border-radius:0 0 .5rem .5rem; padding:1rem 1.25rem; font-size:1rem; line-height:1.75; outline:none; }
.tiptap-editor:focus { border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,.2); }
.tiptap-editor p { margin:0 0 .7em; }
.tiptap-editor h2 { font-size:1.35rem; font-weight:700; margin:1em 0 .4em; }
.tiptap-editor h3 { font-size:1.15rem; font-weight:600; margin:1em 0 .4em; }
.tiptap-editor ul { list-style:disc; padding-left:1.5em; margin:.4em 0; }
.tiptap-editor ol { list-style:decimal; padding-left:1.5em; margin:.4em 0; }
.tiptap-editor a { color:#2563eb; text-decoration:underline; }
.section-card { border:1px solid #e5e7eb; border-radius:.75rem; background:#fff; }
.section-card:hover { border-color:#d1d5db; }
.layout-card { border:2px solid #e5e7eb; border-radius:.75rem; padding:1rem; cursor:pointer; transition:all .15s; }
.layout-card:hover { border-color:#94a3b8; }
.layout-card.selected { border-color:#3b82f6; background:#eff6ff; }
</style>
@endpush

@section('content')

@php $activeTab = request('tab', 'builder'); @endphp

<div x-data="{ tab: '{{ $activeTab }}', fieldModal: false, editingField: null }"
     x-cloak>

{{-- ── Header ─────────────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('contensio::admin.contact.title') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('contensio::admin.contact.subtitle') }}</p>
    </div>
    <a href="{{ route('contensio.account.contact.messages.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
        <i class="bi bi-inbox"></i>
        {{ __('contensio::admin.contact.view_messages') }}
        @if($unread > 0)
        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-semibold bg-amber-500 text-white">
            {{ $unread }}
        </span>
        @endif
    </a>
</div>

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
    {{ session('error') }}
</div>
@endif

{{-- ── Tabs ────────────────────────────────────────────────────────────────── --}}
<div class="flex gap-1 mb-6 bg-gray-100 p-1 rounded-xl w-fit">
    @foreach(['builder' => __('contensio::admin.contact.tab_builder'), 'fields' => __('contensio::admin.contact.tab_fields'), 'appearance' => __('contensio::admin.contact.tab_appearance'), 'settings' => __('contensio::admin.contact.tab_settings')] as $key => $label)
    <button @click="tab = '{{ $key }}'"
            :class="tab === '{{ $key }}' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-2 rounded-lg text-sm transition-all">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: BUILDER                                                              --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'builder'" x-cloak>
<form id="builder-form" method="POST" action="{{ route('contensio.account.contact.builder') }}">
@csrf
@php
    $sections = $settings['sections'];
    if (empty($sections)) {
        $sections = [
            ['id' => \Illuminate\Support\Str::uuid(), 'type' => 'text', 'data' => (object)[]],
            ['id' => \Illuminate\Support\Str::uuid(), 'type' => 'form'],
        ];
    }
@endphp

<div x-data="contactBuilder(@js($sections), @js($languages->map(fn($l) => ['id' => $l->id, 'code' => $l->code, 'name' => $l->name])->values()))"
     class="space-y-4">

    {{-- Sections list --}}
    <div class="space-y-3" id="sections-list"
         x-init="$nextTick(() => initSectionsSortable($el, ids => reorderSections(ids)))">
        <template x-for="(section, index) in sections" :key="section.id">
            <div class="section-card" :data-id="section.id">
                {{-- Section header --}}
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
                    <i class="bi bi-grip-vertical text-gray-300 cursor-move"></i>
                    <span class="text-sm font-semibold text-gray-700 capitalize" x-text="sectionLabel(section.type)"></span>
                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" @click="toggleSection(index)"
                                class="text-gray-400 hover:text-gray-600">
                            <i class="bi" :class="section.open !== false ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </button>
                        <button type="button" @click="requestDeleteSection(index)"
                                x-show="section.type !== 'form'"
                                class="text-gray-400 hover:text-red-500 transition-colors">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                {{-- Section body --}}
                <div x-show="section.open !== false" class="p-4">

                    {{-- TEXT section --}}
                    <template x-if="section.type === 'text'">
                        <div>
                            {{-- Language tabs if multilingual --}}
                            @if($languages->count() > 1)
                            <div x-data="{ activeLang: '{{ $languages->first()->code }}' }" class="space-y-3">
                                <div class="flex gap-1 mb-3">
                                    @foreach($languages as $lang)
                                    <button type="button"
                                            @click="activeLang = '{{ $lang->code }}'"
                                            :class="activeLang === '{{ $lang->code }}' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                            class="px-3 py-1 rounded text-xs font-semibold transition-colors">
                                        {{ strtoupper($lang->code) }}
                                    </button>
                                    @endforeach
                                </div>
                                @foreach($languages as $lang)
                                <div x-show="activeLang === '{{ $lang->code }}'">
                                    <div class="tiptap-toolbar">
                                        <button type="button" @click="execCmd('bold')" title="Bold"><i class="bi bi-type-bold"></i></button>
                                        <button type="button" @click="execCmd('italic')" title="Italic"><i class="bi bi-type-italic"></i></button>
                                        <button type="button" @click="execCmd('underline')" title="Underline"><i class="bi bi-type-underline"></i></button>
                                        <span class="sep"></span>
                                        <button type="button" @click="execCmd('insertUnorderedList')" title="List"><i class="bi bi-list-ul"></i></button>
                                        <button type="button" @click="execCmd('insertOrderedList')" title="Ordered list"><i class="bi bi-list-ol"></i></button>
                                        <span class="sep"></span>
                                        <button type="button" @click="execCmd('formatBlock', 'h2')" title="H2">H2</button>
                                        <button type="button" @click="execCmd('formatBlock', 'h3')" title="H3">H3</button>
                                        <button type="button" @click="execCmd('formatBlock', 'p')" title="Paragraph"><i class="bi bi-paragraph"></i></button>
                                        <span class="sep"></span>
                                        <button type="button" title="Clean formatting"
                                                @click="cleanEditorEl($event.currentTarget.closest('.tiptap-toolbar').nextElementSibling)">
                                            <i class="bi bi-eraser"></i>
                                        </button>
                                    </div>
                                    <div class="tiptap-editor"
                                         contenteditable="true"
                                         @input="updateTextContent(section, '{{ $lang->code }}', $event.target.innerHTML)"
                                         @paste.prevent="pasteClean($event)"
                                         x-init="$el.innerHTML = section.data?.['{{ $lang->code }}'] || ''"
                                         id="text-editor-{{ $lang->code }}-index"></div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            @php $firstLang = $languages->first(); @endphp
                            <div>
                                <div class="tiptap-toolbar">
                                    <button type="button" @click="execCmd('bold')"><i class="bi bi-type-bold"></i></button>
                                    <button type="button" @click="execCmd('italic')"><i class="bi bi-type-italic"></i></button>
                                    <button type="button" @click="execCmd('underline')"><i class="bi bi-type-underline"></i></button>
                                    <span class="sep"></span>
                                    <button type="button" @click="execCmd('insertUnorderedList')"><i class="bi bi-list-ul"></i></button>
                                    <button type="button" @click="execCmd('insertOrderedList')"><i class="bi bi-list-ol"></i></button>
                                    <span class="sep"></span>
                                    <button type="button" @click="execCmd('formatBlock', 'h2')">H2</button>
                                    <button type="button" @click="execCmd('formatBlock', 'h3')">H3</button>
                                    <button type="button" @click="execCmd('formatBlock', 'p')"><i class="bi bi-paragraph"></i></button>
                                    <span class="sep"></span>
                                    <button type="button" title="Clean formatting"
                                            @click="cleanEditorEl($event.currentTarget.closest('.tiptap-toolbar').nextElementSibling)">
                                        <i class="bi bi-eraser"></i>
                                    </button>
                                </div>
                                <div class="tiptap-editor"
                                     contenteditable="true"
                                     @input="updateTextContent(section, '{{ $firstLang?->code ?? 'en' }}', $event.target.innerHTML)"
                                     @paste.prevent="pasteClean($event)"
                                     x-init="$el.innerHTML = section.data?.['{{ $firstLang?->code ?? 'en' }}'] || ''"></div>
                            </div>
                            @endif
                        </div>
                    </template>

                    {{-- ACCORDION section --}}
                    <template x-if="section.type === 'accordion'">
                        <div class="space-y-3">
                            <template x-for="(item, itemIdx) in (section.data?.items ?? [])" :key="itemIdx">
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide"
                                              x-text="`Item ${itemIdx + 1}`"></span>
                                        <button type="button" @click="requestDeleteItem(section, itemIdx)"
                                                class="p-1 text-gray-300 hover:text-red-500 transition-colors">
                                            <i class="bi bi-trash text-sm"></i>
                                        </button>
                                    </div>
                                    @foreach($languages as $lang)
                                    <div class="{{ !$loop->first ? 'mt-3 pt-3 border-t border-gray-100' : '' }} space-y-2">
                                        @if($languages->count() > 1)
                                        <span class="inline-block text-xs font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ strtoupper($lang->code) }}</span>
                                        @endif
                                        <input type="text"
                                               :value="item.title?.['{{ $lang->code }}'] ?? ''"
                                               @input="item.title = { ...(item.title ?? {}), '{{ $lang->code }}': $event.target.value }"
                                               placeholder="{{ __('contensio::admin.contact.accordion_title_placeholder') }}"
                                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <textarea rows="2"
                                                  :value="item.content?.['{{ $lang->code }}'] ?? ''"
                                                  @input="item.content = { ...(item.content ?? {}), '{{ $lang->code }}': $event.target.value }"
                                                  placeholder="{{ __('contensio::admin.contact.accordion_content_placeholder') }}"
                                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                    </div>
                                    @endforeach
                                </div>
                            </template>

                            <button type="button" @click="addAccordionItem(section)"
                                    class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 rounded-lg border border-dashed border-gray-300 bg-white text-sm text-gray-500 hover:border-gray-400 hover:text-gray-700 transition-colors">
                                <i class="bi bi-plus text-base leading-none"></i>
                                {{ __('contensio::admin.contact.accordion_add_item') }}
                            </button>
                        </div>
                    </template>

                    {{-- MAP section --}}
                    <template x-if="section.type === 'map'">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('contensio::admin.contact.map_address') }}</label>
                                <input type="text"
                                       :value="section.data?.address || ''"
                                       @input="section.data = { ...(section.data || {}), address: $event.target.value }"
                                       placeholder="123 Main St, New York, NY 10001"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-400 mt-1">{{ __('contensio::admin.contact.map_address_hint') }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('contensio::admin.contact.map_zoom') }} (<span x-text="section.data?.zoom || 14"></span>)</label>
                                <input type="range" min="1" max="20"
                                       :value="section.data?.zoom || 14"
                                       @input="section.data = { ...(section.data || {}), zoom: parseInt($event.target.value) }"
                                       class="w-full mt-2">
                            </div>
                        </div>
                    </template>

                    {{-- FORM placeholder --}}
                    <template x-if="section.type === 'form'">
                        <div class="flex items-center gap-3 bg-blue-50 rounded-lg px-4 py-3 text-sm text-blue-700">
                            <i class="bi bi-ui-checks text-blue-500 text-lg"></i>
                            <span>{{ __('contensio::admin.contact.form_placeholder') }}</span>
                            <button type="button" @click="tab = 'fields'; $nextTick(() => window.scrollTo(0,0))"
                                    class="ml-auto text-xs font-semibold text-blue-600 underline underline-offset-2">
                                {{ __('contensio::admin.contact.configure_fields') }} →
                            </button>
                        </div>
                    </template>

                    {{-- CONTACT INFO section --}}
                    <template x-if="section.type === 'contact_info'">
                        <div class="space-y-3">
                            <template x-for="(item, itemIdx) in (section.data?.items ?? [])" :key="itemIdx">
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide" x-text="`Item ${itemIdx + 1}`"></span>
                                        <button type="button" @click="removeInfoItem(section, itemIdx)"
                                                class="p-1 text-gray-300 hover:text-red-500 transition-colors">
                                            <i class="bi bi-trash text-sm"></i>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Icon <span class="font-normal text-gray-400">(Bootstrap Icons class)</span></label>
                                            <input type="text"
                                                   :value="item.icon || ''"
                                                   @input="item.icon = $event.target.value"
                                                   placeholder="bi-geo-alt"
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Link <span class="font-normal text-gray-400">(optional — tel:, mailto:, or https://)</span></label>
                                            <input type="text"
                                                   :value="item.link || ''"
                                                   @input="item.link = $event.target.value"
                                                   placeholder="tel:+15550000000"
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    @foreach($languages as $lang)
                                    <div class="{{ !$loop->first ? 'mt-3 pt-3 border-t border-gray-100' : '' }} grid grid-cols-2 gap-3">
                                        @if($languages->count() > 1)
                                        <div class="col-span-2">
                                            <span class="inline-block text-xs font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ strtoupper($lang->code) }}</span>
                                        </div>
                                        @endif
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Label</label>
                                            <input type="text"
                                                   :value="item.label?.['{{ $lang->code }}'] ?? ''"
                                                   @input="item.label = { ...(item.label ?? {}), '{{ $lang->code }}': $event.target.value }"
                                                   placeholder="Address"
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Value</label>
                                            <input type="text"
                                                   :value="item.value?.['{{ $lang->code }}'] ?? ''"
                                                   @input="item.value = { ...(item.value ?? {}), '{{ $lang->code }}': $event.target.value }"
                                                   placeholder="123 Main St, City"
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </template>
                            <button type="button" @click="addInfoItem(section)"
                                    class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 rounded-lg border border-dashed border-gray-300 bg-white text-sm text-gray-500 hover:border-gray-400 hover:text-gray-700 transition-colors">
                                <i class="bi bi-plus text-base leading-none"></i>
                                Add item
                            </button>
                        </div>
                    </template>

                    {{-- BUSINESS HOURS section --}}
                    <template x-if="section.type === 'hours'">
                        <div class="space-y-4">
                            @foreach($languages as $lang)
                            <div class="{{ !$loop->first ? 'pt-3 border-t border-gray-100' : '' }}">
                                @if($languages->count() > 1)
                                <span class="inline-block text-xs font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded mb-2">{{ strtoupper($lang->code) }}</span>
                                @endif
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Section title <span class="font-normal text-gray-400">(optional)</span></label>
                                    <input type="text"
                                           :value="section.data?.title?.['{{ $lang->code }}'] ?? ''"
                                           @input="section.data = { ...(section.data || {}), title: { ...(section.data?.title ?? {}), '{{ $lang->code }}': $event.target.value } }"
                                           placeholder="Business Hours"
                                           class="w-full max-w-xs px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            @endforeach

                            <div class="space-y-2">
                                <div class="grid gap-2 text-xs font-semibold text-gray-400 px-1 pb-1 border-b border-gray-100"
                                     style="grid-template-columns: 1fr 1fr auto;">
                                    <span>Day</span><span>Hours</span><span class="w-7"></span>
                                </div>
                                <template x-for="(row, rowIdx) in (section.data?.rows ?? [])" :key="rowIdx">
                                    <div>
                                        @foreach($languages as $lang)
                                        <div class="flex items-center gap-2 {{ !$loop->first ? 'mt-1' : '' }}">
                                            @if($languages->count() > 1)
                                            <span class="text-xs font-bold text-gray-400 w-6 shrink-0">{{ strtoupper($lang->code) }}</span>
                                            @endif
                                            <input type="text"
                                                   :value="row.day?.['{{ $lang->code }}'] ?? ''"
                                                   @input="row.day = { ...(row.day ?? {}), '{{ $lang->code }}': $event.target.value }"
                                                   placeholder="Monday – Friday"
                                                   class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <input type="text"
                                                   :value="row.hours?.['{{ $lang->code }}'] ?? ''"
                                                   @input="row.hours = { ...(row.hours ?? {}), '{{ $lang->code }}': $event.target.value }"
                                                   placeholder="9:00 AM – 6:00 PM"
                                                   class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            @if($loop->first)
                                            <button type="button" @click="removeHoursRow(section, rowIdx)"
                                                    class="p-1 text-gray-300 hover:text-red-500 transition-colors w-7 shrink-0">
                                                <i class="bi bi-trash text-sm"></i>
                                            </button>
                                            @else
                                            <span class="w-7 shrink-0"></span>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addHoursRow(section)"
                                    class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 rounded-lg border border-dashed border-gray-300 bg-white text-sm text-gray-500 hover:border-gray-400 hover:text-gray-700 transition-colors">
                                <i class="bi bi-plus text-base leading-none"></i>
                                Add row
                            </button>
                        </div>
                    </template>

                    {{-- SOCIAL LINKS section --}}
                    <template x-if="section.type === 'social'">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @php
                            $socialPlatforms = [
                                'twitter'   => ['label' => 'Twitter / X',  'icon' => 'bi-twitter-x',  'ph' => 'https://x.com/username'],
                                'linkedin'  => ['label' => 'LinkedIn',      'icon' => 'bi-linkedin',   'ph' => 'https://linkedin.com/in/username'],
                                'facebook'  => ['label' => 'Facebook',      'icon' => 'bi-facebook',   'ph' => 'https://facebook.com/page'],
                                'instagram' => ['label' => 'Instagram',     'icon' => 'bi-instagram',  'ph' => 'https://instagram.com/username'],
                                'youtube'   => ['label' => 'YouTube',       'icon' => 'bi-youtube',    'ph' => 'https://youtube.com/@channel'],
                                'github'    => ['label' => 'GitHub',        'icon' => 'bi-github',     'ph' => 'https://github.com/username'],
                                'tiktok'    => ['label' => 'TikTok',        'icon' => 'bi-tiktok',     'ph' => 'https://tiktok.com/@username'],
                                'pinterest' => ['label' => 'Pinterest',     'icon' => 'bi-pinterest',  'ph' => 'https://pinterest.com/username'],
                                'whatsapp'  => ['label' => 'WhatsApp',      'icon' => 'bi-whatsapp',   'ph' => '+1234567890 or chat link'],
                            ];
                            @endphp
                            @foreach($socialPlatforms as $platform => $info)
                            <div class="flex items-center gap-2">
                                <i class="bi {{ $info['icon'] }} text-gray-400 text-base w-5 text-center shrink-0"></i>
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">{{ $info['label'] }}</label>
                                    <input type="text"
                                           :value="section.data?.links?.['{{ $platform }}'] ?? ''"
                                           @input="section.data = { ...(section.data || {}), links: { ...(section.data?.links ?? {}), '{{ $platform }}': $event.target.value } }"
                                           placeholder="{{ $info['ph'] }}"
                                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </template>

                    {{-- IMAGE section --}}
                    <template x-if="section.type === 'image'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Image URL</label>
                                <input type="text"
                                       :value="section.data?.url || ''"
                                       @input="section.data = { ...(section.data || {}), url: $event.target.value }"
                                       placeholder="https://example.com/photo.jpg"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            @foreach($languages as $lang)
                            <div class="{{ !$loop->first ? 'pt-3 border-t border-gray-100' : '' }} grid grid-cols-2 gap-3">
                                @if($languages->count() > 1)
                                <div class="col-span-2">
                                    <span class="inline-block text-xs font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ strtoupper($lang->code) }}</span>
                                </div>
                                @endif
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Alt text</label>
                                    <input type="text"
                                           :value="section.data?.alt?.['{{ $lang->code }}'] ?? ''"
                                           @input="section.data = { ...(section.data || {}), alt: { ...(section.data?.alt ?? {}), '{{ $lang->code }}': $event.target.value } }"
                                           placeholder="Describe the image"
                                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Caption <span class="font-normal text-gray-400">(optional)</span></label>
                                    <input type="text"
                                           :value="section.data?.caption?.['{{ $lang->code }}'] ?? ''"
                                           @input="section.data = { ...(section.data || {}), caption: { ...(section.data?.caption ?? {}), '{{ $lang->code }}': $event.target.value } }"
                                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            @endforeach
                            <label class="inline-flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                                <input type="checkbox"
                                       :checked="section.data?.rounded ?? true"
                                       @change="section.data = { ...(section.data || {}), rounded: $event.target.checked }"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Rounded corners
                            </label>
                        </div>
                    </template>

                    {{-- TEAM section --}}
                    <template x-if="section.type === 'team'">
                        <div class="space-y-3">
                            <template x-for="(member, mIdx) in (section.data?.members ?? [])" :key="mIdx">
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide" x-text="`Member ${mIdx + 1}`"></span>
                                        <button type="button" @click="removeTeamMember(section, mIdx)"
                                                class="p-1 text-gray-300 hover:text-red-500 transition-colors">
                                            <i class="bi bi-trash text-sm"></i>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Name</label>
                                            <input type="text"
                                                   :value="member.name || ''"
                                                   @input="member.name = $event.target.value"
                                                   placeholder="Jane Doe"
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Photo URL</label>
                                            <input type="text"
                                                   :value="member.photo || ''"
                                                   @input="member.photo = $event.target.value"
                                                   placeholder="https://..."
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Email <span class="font-normal text-gray-400">(optional)</span></label>
                                            <input type="email"
                                                   :value="member.email || ''"
                                                   @input="member.email = $event.target.value"
                                                   placeholder="jane@example.com"
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Phone <span class="font-normal text-gray-400">(optional)</span></label>
                                            <input type="text"
                                                   :value="member.phone || ''"
                                                   @input="member.phone = $event.target.value"
                                                   placeholder="+1 555 000 0000"
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    @foreach($languages as $lang)
                                    <div class="grid grid-cols-2 gap-3 mt-3 {{ !$loop->first ? 'pt-3 border-t border-gray-100' : '' }}">
                                        @if($languages->count() > 1)
                                        <div class="col-span-2">
                                            <span class="inline-block text-xs font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ strtoupper($lang->code) }}</span>
                                        </div>
                                        @endif
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Role / Position</label>
                                            <input type="text"
                                                   :value="member.role?.['{{ $lang->code }}'] ?? ''"
                                                   @input="member.role = { ...(member.role ?? {}), '{{ $lang->code }}': $event.target.value }"
                                                   placeholder="CEO"
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Short bio</label>
                                            <input type="text"
                                                   :value="member.bio?.['{{ $lang->code }}'] ?? ''"
                                                   @input="member.bio = { ...(member.bio ?? {}), '{{ $lang->code }}': $event.target.value }"
                                                   placeholder="A brief description..."
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </template>
                            <button type="button" @click="addTeamMember(section)"
                                    class="inline-flex items-center justify-center gap-1.5 w-full px-3 py-2 rounded-lg border border-dashed border-gray-300 bg-white text-sm text-gray-500 hover:border-gray-400 hover:text-gray-700 transition-colors">
                                <i class="bi bi-plus text-base leading-none"></i>
                                Add team member
                            </button>
                        </div>
                    </template>

                    {{-- CTA BUTTON section --}}
                    <template x-if="section.type === 'cta'">
                        <div class="space-y-4">
                            @foreach($languages as $lang)
                            <div class="{{ !$loop->first ? 'pt-3 border-t border-gray-100' : '' }} space-y-3">
                                @if($languages->count() > 1)
                                <span class="inline-block text-xs font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ strtoupper($lang->code) }}</span>
                                @endif
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Button label</label>
                                    <input type="text"
                                           :value="section.data?.label?.['{{ $lang->code }}'] ?? ''"
                                           @input="section.data = { ...(section.data || {}), label: { ...(section.data?.label ?? {}), '{{ $lang->code }}': $event.target.value } }"
                                           placeholder="Book a Call"
                                           class="w-full max-w-xs px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Description <span class="font-normal text-gray-400">(optional, shown above button)</span></label>
                                    <input type="text"
                                           :value="section.data?.description?.['{{ $lang->code }}'] ?? ''"
                                           @input="section.data = { ...(section.data || {}), description: { ...(section.data?.description ?? {}), '{{ $lang->code }}': $event.target.value } }"
                                           placeholder="Schedule a 30-minute call with our team"
                                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            @endforeach
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">URL</label>
                                    <input type="text"
                                           :value="section.data?.url || ''"
                                           @input="section.data = { ...(section.data || {}), url: $event.target.value }"
                                           placeholder="https://calendly.com/..."
                                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Style</label>
                                    <select :value="section.data?.style || 'primary'"
                                            @change="section.data = { ...(section.data || {}), style: $event.target.value }"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="primary">Primary</option>
                                        <option value="secondary">Secondary</option>
                                        <option value="outline">Outline</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">Alignment</label>
                                    <select :value="section.data?.align || 'left'"
                                            @change="section.data = { ...(section.data || {}), align: $event.target.value }"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="left">Left</option>
                                        <option value="center">Center</option>
                                        <option value="right">Right</option>
                                    </select>
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                                <input type="checkbox"
                                       :checked="section.data?.new_tab ?? false"
                                       @change="section.data = { ...(section.data || {}), new_tab: $event.target.checked }"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Open in new tab
                            </label>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Add section buttons --}}
    <div class="pt-2 space-y-2">
        <span class="text-xs text-gray-400 font-medium uppercase tracking-wide">{{ __('contensio::admin.contact.add_section') }}</span>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="addSection('text')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="bi bi-type text-gray-400"></i> Text
            </button>
            <button type="button" @click="addSection('contact_info')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="bi bi-info-circle text-gray-400"></i> Contact Info
            </button>
            <button type="button" @click="addSection('hours')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="bi bi-clock text-gray-400"></i> Business Hours
            </button>
            <button type="button" @click="addSection('social')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="bi bi-share text-gray-400"></i> Social Links
            </button>
            <button type="button" @click="addSection('image')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="bi bi-image text-gray-400"></i> Image
            </button>
            <button type="button" @click="addSection('team')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="bi bi-people text-gray-400"></i> Team Cards
            </button>
            <button type="button" @click="addSection('cta')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="bi bi-cursor text-gray-400"></i> CTA Button
            </button>
            <button type="button" @click="addSection('map')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="bi bi-map text-gray-400"></i> Map
            </button>
            <button type="button" @click="addSection('accordion')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="bi bi-chevron-bar-expand text-gray-400"></i> Collapsible
            </button>
        </div>
    </div>

    {{-- Hidden input for sections JSON --}}
    <input type="hidden" name="sections" :value="JSON.stringify(sections)">

    <div class="pt-4">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors shadow-sm">
            {{ __('contensio::admin.contact.save_builder') }}
        </button>
    </div>

    {{-- Delete accordion item confirm modal --}}
    <div x-show="confirmDeleteItem" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="cancelDeleteItem()">
        <div class="absolute inset-0 bg-black/50" @click="cancelDeleteItem()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-50 shrink-0">
                    <i class="bi bi-trash text-red-500 text-lg"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">{{ __('contensio::admin.contact.delete_item_title') }}</h3>
            </div>
            <p class="text-sm text-gray-500 mb-6">{{ __('contensio::admin.contact.delete_item_desc') }}</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="cancelDeleteItem()"
                        class="px-4 py-2 text-sm font-semibold text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('contensio::admin.cancel') }}
                </button>
                <button type="button" @click="confirmDeleteItemAction()"
                        class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors shadow-sm">
                    {{ __('contensio::admin.contact.delete_block_confirm') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Delete section confirm modal --}}
    <div x-show="confirmDelete" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="cancelDeleteSection()">
        <div class="absolute inset-0 bg-black/50" @click="cancelDeleteSection()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-50 shrink-0">
                    <i class="bi bi-trash text-red-500 text-lg"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">{{ __('contensio::admin.contact.delete_block_title') }}</h3>
            </div>
            <p class="text-sm text-gray-500 mb-6">{{ __('contensio::admin.contact.delete_block_desc') }}</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="cancelDeleteSection()"
                        class="px-4 py-2 text-sm font-semibold text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('contensio::admin.cancel') }}
                </button>
                <button type="button" @click="confirmDeleteSection()"
                        class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors shadow-sm">
                    {{ __('contensio::admin.contact.delete_block_confirm') }}
                </button>
            </div>
        </div>
    </div>
</div>
</form>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: FIELDS                                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'fields'" x-cloak>
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ __('contensio::admin.contact.fields_hint') }}</p>
    <button type="button" @click="fieldModal = true; editingField = null"
            class="inline-flex items-center gap-2 px-4 py-2 bg-ember-500 hover:bg-ember-600 text-white text-sm font-semibold rounded-md transition-colors shadow-sm">
        <i class="bi bi-plus"></i>
        {{ __('contensio::admin.contact.add_field') }}
    </button>
</div>

<div id="fields-list" class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
    @foreach($fields as $field)
    @php $trans = $field->translationFor($languages->first()?->id); @endphp
    <div class="flex items-center gap-4 px-5 py-3.5" data-field-id="{{ $field->id }}">
        <i class="bi bi-grip-vertical text-gray-300 cursor-move shrink-0"></i>

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-800">{{ $trans?->label ?: ucfirst($field->key) }}</span>
                @if($field->is_default)
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">default</span>
                @endif
                @if($field->required)
                <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full font-medium">required</span>
                @endif
            </div>
            <p class="text-xs text-gray-400 mt-0.5">
                <span class="font-mono">{{ $field->key }}</span> &middot; {{ $field->type }}
                @if(($field->width ?? 'full') !== 'full')
                &middot; <span class="text-gray-500">{{ $field->width }}</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button"
                    @click="editingField = @js([
                        'id'          => $field->id,
                        'key'         => $field->key,
                        'type'        => $field->type,
                        'required'    => $field->required,
                        'width'       => $field->width ?? 'full',
                        'is_default'  => $field->is_default,
                        'options'     => $field->options,
                        'conditional' => $field->conditional,
                        'labels'       => $fields->find($field->id)?->translations->pluck('label',       'language_id'),
                        'placeholders' => $fields->find($field->id)?->translations->pluck('placeholder', 'language_id'),
                        'help_texts'   => $fields->find($field->id)?->translations->pluck('help_text',   'language_id'),
                    ]); fieldModal = true"
                    class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="bi bi-pencil text-sm"></i>
            </button>
            @if(! $field->is_default)
            <form method="POST" action="{{ route('contensio.account.contact.fields.destroy', $field->id) }}"
                  onsubmit="return confirm('Delete this field?')">
                @csrf @method('DELETE')
                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                    <i class="bi bi-trash text-sm"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: APPEARANCE                                                           --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'appearance'" x-cloak>
<form method="POST" action="{{ route('contensio.account.contact.appearance') }}">
@csrf
@php
    $appearance = $settings['appearance'];
    $fieldSize  = $appearance['field_size'] ?? 'normal';
    $layout     = $appearance['layout'] ?? 'classic';
@endphp

<div class="space-y-8">

    {{-- Field size --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">{{ __('contensio::admin.contact.field_size') }}</h3>
        <div class="flex gap-4">
            @foreach(['small' => __('contensio::admin.contact.size_small'), 'normal' => __('contensio::admin.contact.size_normal'), 'large' => __('contensio::admin.contact.size_large')] as $size => $label)
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="field_size" value="{{ $size }}" {{ $fieldSize === $size ? 'checked' : '' }}
                       class="text-blue-600">
                <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Layout templates --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">{{ __('contensio::admin.contact.layout_template') }}</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

            {{-- Classic --}}
            <label class="cursor-pointer">
                <input type="radio" name="layout" value="classic" {{ $layout === 'classic' ? 'checked' : '' }} class="sr-only peer">
                <div class="layout-card peer-checked:border-blue-500 peer-checked:bg-blue-50">
                    <div class="space-y-1.5 mb-3">
                        <div class="h-2 bg-gray-200 rounded w-full"></div>
                        <div class="h-2 bg-gray-200 rounded w-full"></div>
                        <div class="h-8 bg-gray-200 rounded w-full"></div>
                        <div class="h-2 bg-gray-200 rounded w-full"></div>
                        <div class="h-5 bg-gray-300 rounded w-1/3 mt-2"></div>
                    </div>
                    <p class="text-xs text-center font-medium text-gray-600">{{ __('contensio::admin.contact.layout_classic') }}</p>
                </div>
            </label>

            {{-- Wide --}}
            <label class="cursor-pointer">
                <input type="radio" name="layout" value="wide" {{ $layout === 'wide' ? 'checked' : '' }} class="sr-only peer">
                <div class="layout-card peer-checked:border-blue-500 peer-checked:bg-blue-50">
                    <div class="space-y-1.5 mb-3">
                        <div class="grid grid-cols-2 gap-1">
                            <div class="h-2 bg-gray-200 rounded"></div>
                            <div class="h-2 bg-gray-200 rounded"></div>
                        </div>
                        <div class="h-2 bg-gray-200 rounded w-full"></div>
                        <div class="h-8 bg-gray-200 rounded w-full"></div>
                        <div class="h-5 bg-gray-300 rounded w-1/3 mt-2"></div>
                    </div>
                    <p class="text-xs text-center font-medium text-gray-600">{{ __('contensio::admin.contact.layout_wide') }}</p>
                </div>
            </label>

            {{-- Split --}}
            <label class="cursor-pointer">
                <input type="radio" name="layout" value="split" {{ $layout === 'split' ? 'checked' : '' }} class="sr-only peer">
                <div class="layout-card peer-checked:border-blue-500 peer-checked:bg-blue-50">
                    <div class="grid grid-cols-2 gap-1.5 mb-3">
                        <div class="space-y-1">
                            <div class="h-1.5 bg-gray-200 rounded"></div>
                            <div class="h-1.5 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-10 bg-blue-100 rounded mt-1"></div>
                        </div>
                        <div class="space-y-1">
                            <div class="h-1.5 bg-gray-200 rounded"></div>
                            <div class="h-1.5 bg-gray-200 rounded"></div>
                            <div class="h-4 bg-gray-200 rounded"></div>
                            <div class="h-3 bg-gray-300 rounded w-2/3"></div>
                        </div>
                    </div>
                    <p class="text-xs text-center font-medium text-gray-600">{{ __('contensio::admin.contact.layout_split') }}</p>
                </div>
            </label>

            {{-- Card --}}
            <label class="cursor-pointer">
                <input type="radio" name="layout" value="card" {{ $layout === 'card' ? 'checked' : '' }} class="sr-only peer">
                <div class="layout-card peer-checked:border-blue-500 peer-checked:bg-blue-50">
                    <div class="border border-gray-200 rounded-lg p-2 space-y-1.5 mb-3">
                        <div class="h-2 bg-gray-200 rounded w-full"></div>
                        <div class="h-2 bg-gray-200 rounded w-full"></div>
                        <div class="h-6 bg-gray-200 rounded w-full"></div>
                        <div class="h-4 bg-gray-300 rounded w-1/3 mt-1"></div>
                    </div>
                    <p class="text-xs text-center font-medium text-gray-600">{{ __('contensio::admin.contact.layout_card') }}</p>
                </div>
            </label>

        </div>
    </div>

    <button type="submit"
            class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors shadow-sm">
        {{ __('contensio::admin.contact.save_appearance') }}
    </button>
</div>
</form>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- TAB: SETTINGS                                                             --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="tab === 'settings'" x-cloak>
<form method="POST" action="{{ route('contensio.account.contact.settings') }}">
@csrf
@php
    $antispam      = $settings['antispam'];
    $notifications = $settings['notifications'];
    $redirect      = $settings['redirect'];
    $fileUploads   = $settings['file_uploads'];
    $gdpr          = $settings['gdpr'];
    $slugs         = $settings['slugs'];
    $successMsgs   = $settings['success_message'];
@endphp

<div class="space-y-6">

    {{-- ── URL & Redirect ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-800">{{ __('contensio::admin.contact.section_url') }}</h3>
        </div>
        <div class="p-6 space-y-5">

            {{-- Slug per language --}}
            @if($languages->count() > 1)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($languages as $lang)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        {{ __('contensio::admin.contact.slug') }} ({{ strtoupper($lang->code) }})
                    </label>
                    <div class="flex items-center gap-1">
                        <span class="text-sm text-gray-400">/</span>
                        <input type="text" name="slug_{{ $lang->code }}"
                               value="{{ $slugs[$lang->code] ?? 'contact' }}"
                               class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('contensio::admin.contact.slug') }}</label>
                <div class="flex items-center gap-1">
                    <span class="text-sm text-gray-400">/</span>
                    <input type="text" name="slug"
                           value="{{ $slugs['en'] ?? $slugs[array_key_first($slugs)] ?? 'contact' }}"
                           class="max-w-xs px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ __('contensio::admin.contact.slug_hint') }}</p>
            </div>
            @endif

            {{-- Success message per language --}}
            @foreach($languages as $lang)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    {{ __('contensio::admin.contact.success_message') }} @if($languages->count() > 1)({{ strtoupper($lang->code) }})@endif
                </label>
                <input type="text" name="success_message_{{ $lang->code }}"
                       value="{{ $successMsgs[$lang->code] ?? '' }}"
                       placeholder="{{ __('contensio::admin.contact.success_message_placeholder') }}"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            @endforeach

            {{-- Redirect --}}
            <div x-data="{ redirectType: '{{ $redirect['type'] ?? 'same_page' }}' }">
                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('contensio::admin.contact.redirect') }}</label>
                <div class="flex gap-4 mb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="redirect_type" value="same_page"
                               {{ ($redirect['type'] ?? 'same_page') === 'same_page' ? 'checked' : '' }}
                               @change="redirectType = 'same_page'" class="text-blue-600">
                        <span class="text-sm text-gray-700">{{ __('contensio::admin.contact.redirect_same_page') }}</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="redirect_type" value="url"
                               {{ ($redirect['type'] ?? '') === 'url' ? 'checked' : '' }}
                               @change="redirectType = 'url'" class="text-blue-600">
                        <span class="text-sm text-gray-700">{{ __('contensio::admin.contact.redirect_url') }}</span>
                    </label>
                </div>
                <div x-show="redirectType === 'url'">
                    <input type="text" name="redirect_url"
                           value="{{ $redirect['url'] ?? '' }}"
                           placeholder="https://example.com/thank-you"
                           class="w-full max-w-md px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Antispam ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-800">{{ __('contensio::admin.contact.section_antispam') }}</h3>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @include('contensio::admin.settings.partials.toggle', [
                    'name'        => 'honeypot',
                    'state'       => ($antispam['honeypot'] ?? true) ? 'true' : 'false',
                    'label'       => __('contensio::admin.contact.antispam_honeypot'),
                    'description' => __('contensio::admin.contact.antispam_honeypot_hint'),
                ])
                @include('contensio::admin.settings.partials.toggle', [
                    'name'        => 'time_check',
                    'state'       => ($antispam['time_check'] ?? true) ? 'true' : 'false',
                    'label'       => __('contensio::admin.contact.antispam_time_check'),
                    'description' => __('contensio::admin.contact.antispam_time_check_hint'),
                ])
            </div>

            {{-- Min seconds --}}
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700">{{ __('contensio::admin.contact.antispam_min_seconds') }}</label>
                <input type="number" name="min_seconds" value="{{ $antispam['min_seconds'] ?? 3 }}" min="1" max="30"
                       class="w-20 px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
            </div>

            {{-- Rate limit --}}
            <div x-data="{ enabled: {{ ($antispam['rate_limit']['enabled'] ?? false) ? 'true' : 'false' }} }">
                <div class="flex items-start gap-4 mb-3">
                    <input type="hidden" name="rate_limit_enabled" :value="enabled ? '1' : '0'">
                    <button type="button" @click="enabled = !enabled"
                            :class="enabled ? 'bg-ember-500' : 'bg-gray-200'"
                            class="relative shrink-0 mt-0.5 inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:ring-offset-2">
                        <span :class="enabled ? 'translate-x-5' : 'translate-x-0.5'"
                              class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                    </button>
                    <span @click="enabled = !enabled" class="text-sm font-medium text-gray-800 cursor-pointer mt-0.5">{{ __('contensio::admin.contact.antispam_rate_limit') }}</span>
                </div>
                <div x-show="enabled" x-cloak class="flex items-center gap-3 ml-[3.75rem]">
                    <span class="text-sm text-gray-500">Max</span>
                    <input type="number" name="rate_limit_max" value="{{ $antispam['rate_limit']['max'] ?? 5 }}" min="1"
                           class="w-20 px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                    <span class="text-sm text-gray-500">submissions per</span>
                    <input type="number" name="rate_limit_minutes" value="{{ $antispam['rate_limit']['per_minutes'] ?? 60 }}" min="1"
                           class="w-20 px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                    <span class="text-sm text-gray-500">min per IP</span>
                </div>
            </div>

            {{-- Math question --}}
            @include('contensio::admin.settings.partials.toggle', [
                'name'  => 'math_question_enabled',
                'state' => ($antispam['math_question']['enabled'] ?? false) ? 'true' : 'false',
                'label' => __('contensio::admin.contact.antispam_math'),
            ])

            {{-- reCAPTCHA --}}
            <div x-data="{ enabled: {{ ($antispam['recaptcha']['enabled'] ?? false) ? 'true' : 'false' }} }">
                <div class="flex items-start gap-4 mb-3">
                    <input type="hidden" name="recaptcha_enabled" :value="enabled ? '1' : '0'">
                    <button type="button" @click="enabled = !enabled"
                            :class="enabled ? 'bg-ember-500' : 'bg-gray-200'"
                            class="relative shrink-0 mt-0.5 inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:ring-offset-2">
                        <span :class="enabled ? 'translate-x-5' : 'translate-x-0.5'"
                              class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                    </button>
                    <span @click="enabled = !enabled" class="text-sm font-medium text-gray-800 cursor-pointer mt-0.5">{{ __('contensio::admin.contact.recaptcha_title') }}</span>
                </div>
                <div x-show="enabled" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 ml-[3.75rem]">
                    <input type="text" name="recaptcha_site_key" value="{{ $antispam['recaptcha']['site_key'] ?? '' }}"
                           placeholder="{{ __('contensio::admin.contact.recaptcha_site_key') }}"
                           class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                    <input type="text" name="recaptcha_secret_key" value="{{ $antispam['recaptcha']['secret_key'] ?? '' }}"
                           placeholder="{{ __('contensio::admin.contact.recaptcha_secret_key') }}"
                           class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                </div>
            </div>

            {{-- Turnstile --}}
            <div x-data="{ enabled: {{ ($antispam['turnstile']['enabled'] ?? false) ? 'true' : 'false' }} }">
                <div class="flex items-start gap-4 mb-3">
                    <input type="hidden" name="turnstile_enabled" :value="enabled ? '1' : '0'">
                    <button type="button" @click="enabled = !enabled"
                            :class="enabled ? 'bg-ember-500' : 'bg-gray-200'"
                            class="relative shrink-0 mt-0.5 inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:ring-offset-2">
                        <span :class="enabled ? 'translate-x-5' : 'translate-x-0.5'"
                              class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                    </button>
                    <span @click="enabled = !enabled" class="text-sm font-medium text-gray-800 cursor-pointer mt-0.5">{{ __('contensio::admin.contact.turnstile_title') }}</span>
                </div>
                <div x-show="enabled" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 ml-[3.75rem]">
                    <input type="text" name="turnstile_site_key" value="{{ $antispam['turnstile']['site_key'] ?? '' }}"
                           placeholder="{{ __('contensio::admin.contact.turnstile_site_key') }}"
                           class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                    <input type="text" name="turnstile_secret_key" value="{{ $antispam['turnstile']['secret_key'] ?? '' }}"
                           placeholder="{{ __('contensio::admin.contact.turnstile_secret_key') }}"
                           class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Notifications ────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-800">{{ __('contensio::admin.contact.section_notifications') }}</h3>
        </div>
        <div class="p-6 space-y-5">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('contensio::admin.contact.notify_admin_email') }}</label>
                <input type="email" name="admin_email"
                       value="{{ $notifications['admin_email'] ?? '' }}"
                       placeholder="office@example.com"
                       class="w-full max-w-sm px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">{{ __('contensio::admin.contact.notify_admin_email_hint') }}</p>
            </div>

            {{-- Auto-reply --}}
            <div x-data="{ enabled: {{ ($notifications['auto_reply']['enabled'] ?? false) ? 'true' : 'false' }} }">
                <div class="flex items-start gap-4 mb-3">
                    <input type="hidden" name="auto_reply_enabled" :value="enabled ? '1' : '0'">
                    <button type="button" @click="enabled = !enabled"
                            :class="enabled ? 'bg-ember-500' : 'bg-gray-200'"
                            class="relative shrink-0 mt-0.5 inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:ring-offset-2">
                        <span :class="enabled ? 'translate-x-5' : 'translate-x-0.5'"
                              class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                    </button>
                    <span @click="enabled = !enabled" class="text-sm font-medium text-gray-800 cursor-pointer mt-0.5">{{ __('contensio::admin.contact.auto_reply') }}</span>
                </div>
                <div x-show="enabled" class="space-y-4 ml-6">
                    @foreach($languages as $lang)
                    <div class="border border-gray-100 rounded-lg p-4 space-y-3">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">{{ $lang->name }} ({{ strtoupper($lang->code) }})</p>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('contensio::admin.contact.auto_reply_subject') }}</label>
                            <input type="text" name="auto_reply_subject_{{ $lang->code }}"
                                   value="{{ $notifications['auto_reply']['subject'][$lang->code] ?? '' }}"
                                   placeholder="{{ __('contensio::admin.contact.auto_reply_subject_placeholder') }}"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('contensio::admin.contact.auto_reply_body') }}</label>
                            <textarea name="auto_reply_body_{{ $lang->code }}" rows="3"
                                      class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $notifications['auto_reply']['body'][$lang->code] ?? '' }}</textarea>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Webhook --}}
            <div x-data="{ enabled: {{ ($notifications['webhook']['enabled'] ?? false) ? 'true' : 'false' }} }">
                <div class="flex items-start gap-4 mb-3">
                    <input type="hidden" name="webhook_enabled" :value="enabled ? '1' : '0'">
                    <button type="button" @click="enabled = !enabled"
                            :class="enabled ? 'bg-ember-500' : 'bg-gray-200'"
                            class="relative shrink-0 mt-0.5 inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:ring-offset-2">
                        <span :class="enabled ? 'translate-x-5' : 'translate-x-0.5'"
                              class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                    </button>
                    <span @click="enabled = !enabled" class="text-sm font-medium text-gray-800 cursor-pointer mt-0.5">{{ __('contensio::admin.contact.webhook') }}</span>
                </div>
                <div x-show="enabled" class="ml-6">
                    <input type="url" name="webhook_url"
                           value="{{ $notifications['webhook']['url'] ?? '' }}"
                           placeholder="https://hooks.zapier.com/..."
                           class="w-full max-w-md px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">{{ __('contensio::admin.contact.webhook_hint') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── File Uploads ─────────────────────────────────────────────────────── --}}
    <div x-data="{ enabled: {{ ($fileUploads['enabled'] ?? false) ? 'true' : 'false' }} }"
         class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <h3 class="text-base font-bold text-gray-800">{{ __('contensio::admin.contact.section_file_uploads') }}</h3>
            <div class="ml-auto flex items-center gap-2.5">
                <input type="hidden" name="file_uploads_enabled" :value="enabled ? '1' : '0'">
                <button type="button" @click="enabled = !enabled"
                        :class="enabled ? 'bg-ember-500' : 'bg-gray-200'"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:ring-offset-2">
                    <span :class="enabled ? 'translate-x-5' : 'translate-x-0.5'"
                          class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                </button>
                <span @click="enabled = !enabled" class="text-sm font-medium text-gray-500 cursor-pointer">{{ __('contensio::admin.contact.enabled') }}</span>
            </div>
        </div>
        <div x-show="enabled" class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('contensio::admin.contact.max_files') }}</label>
                <input type="number" name="max_files" value="{{ $fileUploads['max_files'] ?? 3 }}" min="1" max="10"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('contensio::admin.contact.max_size_mb') }}</label>
                <input type="number" name="max_size_mb" value="{{ $fileUploads['max_size_mb'] ?? 5 }}" min="1" max="50"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('contensio::admin.contact.allowed_types') }}</label>
                <input type="text" name="allowed_types"
                       value="{{ implode(',', (array) ($fileUploads['allowed_types'] ?? ['jpg', 'png', 'pdf'])) }}"
                       placeholder="jpg,png,pdf,docx"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">{{ __('contensio::admin.contact.allowed_types_hint') }}</p>
            </div>
        </div>
    </div>

    {{-- ── GDPR ─────────────────────────────────────────────────────────────── --}}
    <div x-data="{ enabled: {{ ($gdpr['enabled'] ?? false) ? 'true' : 'false' }} }"
         class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <h3 class="text-base font-bold text-gray-800">{{ __('contensio::admin.contact.section_gdpr') }}</h3>
            <div class="ml-auto flex items-center gap-2.5">
                <input type="hidden" name="gdpr_enabled" :value="enabled ? '1' : '0'">
                <button type="button" @click="enabled = !enabled"
                        :class="enabled ? 'bg-ember-500' : 'bg-gray-200'"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:ring-offset-2">
                    <span :class="enabled ? 'translate-x-5' : 'translate-x-0.5'"
                          class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                </button>
                <span @click="enabled = !enabled" class="text-sm font-medium text-gray-500 cursor-pointer">{{ __('contensio::admin.contact.enabled') }}</span>
            </div>
        </div>
        <div x-show="enabled" class="p-6 space-y-4">
            @include('contensio::admin.settings.partials.toggle', [
                'name'  => 'gdpr_required',
                'state' => ($gdpr['required'] ?? true) ? 'true' : 'false',
                'label' => __('contensio::admin.contact.gdpr_required'),
            ])
            @foreach($languages as $lang)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    {{ __('contensio::admin.contact.gdpr_text') }} @if($languages->count() > 1)({{ strtoupper($lang->code) }})@endif
                </label>
                <input type="text" name="gdpr_text_{{ $lang->code }}"
                       value="{{ $gdpr['text'][$lang->code] ?? '' }}"
                       placeholder="{{ __('contensio::admin.contact.gdpr_text_placeholder') }}"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            @endforeach
        </div>
    </div>

    <button type="submit"
            class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors shadow-sm">
        {{ __('contensio::admin.contact.save_settings') }}
    </button>
</div>
</form>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- FIELD MODAL                                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="fieldModal" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @keydown.escape.window="fieldModal = false">
    <div class="absolute inset-0 bg-black/50" @click="fieldModal = false"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900"
                x-text="editingField ? '{{ __('contensio::admin.contact.edit_field') }}' : '{{ __('contensio::admin.contact.add_field') }}'"></h3>
            <button @click="fieldModal = false" class="p-1.5 text-gray-400 hover:text-gray-700 rounded-lg">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Add field form --}}
        <template x-if="!editingField">
        <form method="POST" action="{{ route('contensio.account.contact.fields.store') }}" class="p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('contensio::admin.contact.field_key') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="key" required placeholder="phone_number"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">{{ __('contensio::admin.contact.field_key_hint') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('contensio::admin.contact.field_type') }} <span class="text-red-500">*</span></label>
                    <select name="type" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['text' => 'Text', 'textarea' => 'Textarea', 'email' => 'Email', 'phone' => 'Phone', 'url' => 'URL', 'date' => 'Date', 'select' => 'Select (one)', 'multiselect' => 'Multi-select', 'checkbox' => 'Checkbox', 'rating' => 'Rating (1-5)', 'file' => 'File upload'] as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Labels per language --}}
            @foreach($languages as $lang)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    {{ __('contensio::admin.contact.field_label') }} @if($languages->count() > 1)({{ strtoupper($lang->code) }})@endif
                </label>
                <input type="text" name="label_{{ $lang->code }}" placeholder="{{ __('contensio::admin.contact.field_label_placeholder') }}"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    {{ __('contensio::admin.contact.field_placeholder') }} @if($languages->count() > 1)({{ strtoupper($lang->code) }})@endif
                </label>
                <input type="text" name="placeholder_{{ $lang->code }}"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    {{ __('contensio::admin.contact.field_description') }} @if($languages->count() > 1)({{ strtoupper($lang->code) }})@endif
                </label>
                <input type="text" name="help_text_{{ $lang->code }}"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            @endforeach

            @include('contensio::admin.settings.partials.toggle', [
                'name'  => 'required',
                'state' => 'false',
                'label' => __('contensio::admin.contact.field_required'),
            ])

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('contensio::admin.contact.field_width') }}</label>
                <div class="grid grid-cols-4 gap-2">
                    @foreach(['full' => __('contensio::admin.contact.width_full'), 'half' => __('contensio::admin.contact.width_half'), '1/3' => __('contensio::admin.contact.width_third'), '1/4' => __('contensio::admin.contact.width_quarter')] as $val => $lbl)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="width" value="{{ $val }}" {{ $val === 'full' ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="text-center py-2 px-1 text-xs font-medium rounded-lg border border-gray-200
                                    peer-checked:border-ember-500 peer-checked:bg-ember-50 peer-checked:text-ember-700
                                    text-gray-600 hover:border-gray-300 transition-colors">
                            {{ $lbl }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="fieldModal = false"
                        class="px-4 py-2 text-sm font-semibold text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('contensio::admin.cancel') }}
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-4 py-2 rounded-md transition-colors shadow-sm">
                    {{ __('contensio::admin.contact.add_field') }}
                </button>
            </div>
        </form>
        </template>

        {{-- Edit field form --}}
        <template x-if="editingField">
        @php $updateFieldBase = \Illuminate\Support\Str::beforeLast(route('contensio.account.contact.fields.update', ['id' => 0]), '/0'); @endphp
        <form method="POST" :action="`{{ $updateFieldBase }}/${editingField.id}`" class="p-6 space-y-5">
            @csrf @method('PUT')
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <span class="font-mono text-sm text-gray-700" x-text="editingField.key"></span>
                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full" x-text="editingField.type"></span>
            </div>

            @foreach($languages as $lang)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    {{ __('contensio::admin.contact.field_label') }} @if($languages->count() > 1)({{ strtoupper($lang->code) }})@endif
                </label>
                <input type="text" name="label_{{ $lang->code }}"
                       :value="editingField.labels?.['{{ $lang->id }}'] || ''"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    {{ __('contensio::admin.contact.field_placeholder') }} @if($languages->count() > 1)({{ strtoupper($lang->code) }})@endif
                </label>
                <input type="text" name="placeholder_{{ $lang->code }}"
                       :value="editingField.placeholders?.['{{ $lang->id }}'] || ''"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    {{ __('contensio::admin.contact.field_description') }} @if($languages->count() > 1)({{ strtoupper($lang->code) }})@endif
                </label>
                <input type="text" name="help_text_{{ $lang->code }}"
                       :value="editingField.help_texts?.['{{ $lang->id }}'] || ''"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            @endforeach

            <div x-data="{ on: editingField?.required ?? false }" class="flex items-start gap-4">
                <input type="hidden" name="required" :value="on ? '1' : '0'">
                <button type="button" @click="on = !on"
                        :class="on ? 'bg-ember-500' : 'bg-gray-200'"
                        class="relative shrink-0 mt-0.5 inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:ring-offset-2">
                    <span :class="on ? 'translate-x-5' : 'translate-x-0.5'"
                          class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200"></span>
                </button>
                <span @click="on = !on" class="text-sm font-medium text-gray-800 cursor-pointer mt-0.5">
                    {{ __('contensio::admin.contact.field_required') }}
                </span>
            </div>

            <div x-data="{ w: editingField?.width ?? 'full' }">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('contensio::admin.contact.field_width') }}</label>
                <input type="hidden" name="width" :value="w">
                <div class="grid grid-cols-4 gap-2">
                    @foreach(['full' => __('contensio::admin.contact.width_full'), 'half' => __('contensio::admin.contact.width_half'), '1/3' => __('contensio::admin.contact.width_third'), '1/4' => __('contensio::admin.contact.width_quarter')] as $val => $lbl)
                    <button type="button" @click="w = '{{ $val }}'"
                            :class="w === '{{ $val }}' ? 'border-ember-500 bg-ember-50 text-ember-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                            class="text-center py-2 px-1 text-xs font-medium rounded-lg border transition-colors">
                        {{ $lbl }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="fieldModal = false"
                        class="px-4 py-2 text-sm font-semibold text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    {{ __('contensio::admin.cancel') }}
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-4 py-2 rounded-md transition-colors shadow-sm">
                    {{ __('contensio::admin.save') }}
                </button>
            </div>
        </form>
        </template>
    </div>
</div>

</div>{{-- end x-data wrapper --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
// ── HTML cleaner — keeps only headings, links, bold, italic, paragraphs, lists ─
function cleanHtml(html) {
    const ALLOWED = {
        H2: [], H3: [], H4: [],
        P: [], BR: [],
        STRONG: [], B: [], EM: [], I: [],
        A: ['href', 'target'],
        UL: [], OL: [], LI: [],
    };

    const doc = new DOMParser().parseFromString(html, 'text/html');

    function walk(node) {
        if (node.nodeType === Node.TEXT_NODE) return node.cloneNode();
        if (node.nodeType !== Node.ELEMENT_NODE) return null;

        const tag = node.tagName;
        const frag = document.createDocumentFragment();
        node.childNodes.forEach(child => {
            const n = walk(child);
            if (n) frag.appendChild(n);
        });

        if (ALLOWED[tag] !== undefined) {
            const el = document.createElement(tag);
            ALLOWED[tag].forEach(attr => {
                if (node.hasAttribute(attr)) el.setAttribute(attr, node.getAttribute(attr));
            });
            el.appendChild(frag);
            return el;
        }

        // Unwanted element — keep its children as-is
        return frag;
    }

    const out = document.createElement('div');
    doc.body.childNodes.forEach(child => {
        const n = walk(child);
        if (n) out.appendChild(n);
    });

    // Collapse empty paragraphs and clean up whitespace
    return out.innerHTML.replace(/<p>\s*<\/p>/g, '').trim();
}

function contactBuilder(initialSections, languages) {
    return {
        sections: initialSections.length ? initialSections : [
            { id: crypto.randomUUID(), type: 'text', data: {}, open: true },
            { id: crypto.randomUUID(), type: 'form', open: true },
        ],
        confirmDelete: false,
        pendingDeleteIdx: null,
        confirmDeleteItem: false,
        pendingDeleteItemSection: null,
        pendingDeleteItemIdx: null,
        sectionLabel(type) {
            return {
                text: 'Text Block', map: 'Google Map', form: 'Contact Form',
                accordion: 'Collapsible Items', contact_info: 'Contact Info',
                hours: 'Business Hours', social: 'Social Links',
                image: 'Image', team: 'Team Cards', cta: 'CTA Button',
            }[type] || type;
        },
        addSection(type) {
            let data = {};
            if (type === 'accordion')     data = { items: [{ title: {}, content: {} }] };
            if (type === 'contact_info')  data = { items: [
                { icon: 'bi-geo-alt',    label: {}, value: {}, link: '' },
                { icon: 'bi-telephone',  label: {}, value: {}, link: '' },
                { icon: 'bi-envelope',   label: {}, value: {}, link: '' },
            ]};
            if (type === 'hours')  data = { title: {}, rows: [{ day: {}, hours: {} }, { day: {}, hours: {} }, { day: {}, hours: {} }] };
            if (type === 'social') data = { links: {} };
            if (type === 'team')   data = { members: [{ name: '', photo: '', email: '', phone: '', role: {}, bio: {} }] };
            if (type === 'cta')    data = { label: {}, url: '', style: 'primary', align: 'left', description: {}, new_tab: false };
            this.sections.push({ id: crypto.randomUUID(), type, data, open: true });
        },
        removeSection(index) {
            this.sections.splice(index, 1);
        },
        requestDeleteSection(index) {
            this.pendingDeleteIdx = index;
            this.confirmDelete = true;
        },
        cancelDeleteSection() {
            this.confirmDelete = false;
            this.pendingDeleteIdx = null;
        },
        confirmDeleteSection() {
            this.removeSection(this.pendingDeleteIdx);
            this.confirmDelete = false;
            this.pendingDeleteIdx = null;
            this.$nextTick(() => document.getElementById('builder-form').submit());
        },
        toggleSection(index) {
            this.sections[index].open = this.sections[index].open === false ? true : false;
        },
        updateTextContent(section, langCode, html) {
            if (!section.data || Array.isArray(section.data)) section.data = {};
            section.data[langCode] = html;
        },
        execCmd(cmd, val = null) {
            document.execCommand(cmd, false, val);
        },
        pasteClean(e) {
            const html  = e.clipboardData.getData('text/html');
            const text  = e.clipboardData.getData('text/plain');
            const clean = html ? cleanHtml(html) : text.replace(/\n{2,}/g, '</p><p>').replace(/\n/g, '<br>');
            document.execCommand('insertHTML', false, clean);
        },
        cleanEditorEl(el) {
            if (!el) return;
            el.innerHTML = cleanHtml(el.innerHTML);
            el.dispatchEvent(new Event('input', { bubbles: true }));
        },
        reorderSections(newIds) {
            const map = Object.fromEntries(this.sections.map(s => [s.id, s]));
            this.sections = newIds.map(id => map[id]).filter(Boolean);
        },
        requestDeleteItem(section, index) {
            this.pendingDeleteItemSection = section;
            this.pendingDeleteItemIdx = index;
            this.confirmDeleteItem = true;
        },
        cancelDeleteItem() {
            this.confirmDeleteItem = false;
            this.pendingDeleteItemSection = null;
            this.pendingDeleteItemIdx = null;
        },
        confirmDeleteItemAction() {
            this.removeAccordionItem(this.pendingDeleteItemSection, this.pendingDeleteItemIdx);
            this.confirmDeleteItem = false;
            this.pendingDeleteItemSection = null;
            this.pendingDeleteItemIdx = null;
            this.$nextTick(() => document.getElementById('builder-form').submit());
        },
        addAccordionItem(section) {
            if (!section.data || Array.isArray(section.data)) section.data = {};
            if (!Array.isArray(section.data.items)) section.data.items = [];
            section.data.items.push({ title: {}, content: {} });
        },
        removeAccordionItem(section, index) {
            section.data.items.splice(index, 1);
        },
        addInfoItem(section) {
            if (!section.data || Array.isArray(section.data)) section.data = {};
            if (!Array.isArray(section.data.items)) section.data.items = [];
            section.data.items.push({ icon: 'bi-info-circle', label: {}, value: {}, link: '' });
        },
        removeInfoItem(section, index) {
            section.data.items.splice(index, 1);
        },
        addHoursRow(section) {
            if (!section.data || Array.isArray(section.data)) section.data = {};
            if (!Array.isArray(section.data.rows)) section.data.rows = [];
            section.data.rows.push({ day: {}, hours: {} });
        },
        removeHoursRow(section, index) {
            section.data.rows.splice(index, 1);
        },
        addTeamMember(section) {
            if (!section.data || Array.isArray(section.data)) section.data = {};
            if (!Array.isArray(section.data.members)) section.data.members = [];
            section.data.members.push({ name: '', photo: '', email: '', phone: '', role: {}, bio: {} });
        },
        removeTeamMember(section, index) {
            section.data.members.splice(index, 1);
        },
    };
}

// ── Sections sortable ────────────────────────────────────────────────────────
function initSectionsSortable(el, reorderCallback) {
    if (typeof Sortable === 'undefined') return;
    Sortable.create(el, {
        handle: '.bi-grip-vertical',
        animation: 150,
        ghostClass: 'opacity-40',
        onEnd({ newIndex, oldIndex }) {
            if (newIndex === oldIndex) return;
            const ids = [...el.children].map(c => c.dataset.id);
            reorderCallback(ids);
        },
    });
}

// ── Fields sortable ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const fieldsList = document.getElementById('fields-list');
    if (!fieldsList || typeof Sortable === 'undefined') return;
    Sortable.create(fieldsList, {
        handle: '.bi-grip-vertical',
        animation: 150,
        ghostClass: 'opacity-40',
        onEnd() {
            const ids = [...fieldsList.querySelectorAll('[data-field-id]')]
                            .map(el => el.dataset.fieldId);
            fetch('{{ route('contensio.account.contact.fields.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ ids }),
            });
        },
    });
});
</script>
@endpush

@endsection
