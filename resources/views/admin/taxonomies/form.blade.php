{{--
 | Contensio - The open content platform for Laravel.
 | Admin — taxonomies form.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', $taxonomy ? 'Edit Taxonomy' : 'New Taxonomy')

@section('breadcrumb')
    <a href="{{ route('cms.admin.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('cms.admin.content-types.index') }}" class="text-gray-500 hover:text-gray-700">Content Types</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">{{ $taxonomy ? 'Edit Taxonomy' : 'New Taxonomy' }}</span>
@endsection

@section('content')

@php
    $defaultLangId = $defaultLanguage?->id ?? $languages->first()?->id ?? 1;
    $multiLang     = $languages->count() > 1;
@endphp

<div class="max-w-lg">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">{{ $taxonomy ? 'Edit Taxonomy' : 'New Taxonomy' }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            For: <span class="font-semibold text-gray-700">{{ $type->translations->first()?->labels['plural'] ?? $type->name }}</span>
        </p>
    </div>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-md px-4 py-3">
        <ul class="text-sm text-red-700 space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $taxonomy
              ? route('cms.admin.taxonomies.update', [$type->id, $taxonomy->id])
              : route('cms.admin.taxonomies.store', $type->id) }}">
        @csrf
        @if($taxonomy) @method('PUT') @endif

        {{-- Labels (per language) --}}
        <div class="bg-white border border-gray-200 rounded-md overflow-hidden mb-4"
             x-data="{ activeLang: {{ $defaultLangId }} }">

            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                <h2 class="text-base font-bold text-gray-800 flex-1">Labels</h2>

                @if($multiLang)
                <div class="flex items-center gap-1">
                    @foreach($languages as $lang)
                    <button type="button"
                            @click="activeLang = {{ $lang->id }}"
                            :class="activeLang === {{ $lang->id }}
                                ? 'bg-slate-800 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-bold transition-colors">
                        {{ strtoupper($lang->code) }}
                        @if($lang->is_default)<span class="opacity-50">★</span>@endif
                        @if($lang->status === 'inactive')<span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block" title="Admin only"></span>@endif
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="px-5 py-5">
                @foreach($languages as $lang)
                @php
                    $defSingular = old("translations.{$lang->id}.singular", $existing[$lang->id]['singular'] ?? '');
                    $defPlural   = old("translations.{$lang->id}.plural",   $existing[$lang->id]['plural']   ?? '');
                    $defSlug     = old("translations.{$lang->id}.slug",     $existing[$lang->id]['slug']     ?? '');
                    $slugTouched = ($defSlug !== '') ? 'true' : 'false';
                    $isDefault   = $lang->id === $defaultLangId;
                @endphp
                <div x-show="activeLang === {{ $lang->id }}" x-cloak="{{ $multiLang ? '' : 'false' }}">
                @php
                    $exCreate   = old("translations.{$lang->id}.labels.create",    $existing[$lang->id]['create']    ?? '');
                    $exAll      = old("translations.{$lang->id}.labels.all",       $existing[$lang->id]['all']       ?? '');
                    $exNotFound = old("translations.{$lang->id}.labels.not_found", $existing[$lang->id]['not_found'] ?? '');
                @endphp
                    <div x-data="{
                            singular: @js($defSingular),
                            plural:   @js($defPlural),
                            slug: @js($defSlug),
                            slugTouched: {{ $slugTouched }},
                            showMore: {{ ($exCreate || $exAll || $exNotFound) ? 'true' : 'false' }},
                            lbl: {
                                create:    @js($exCreate),
                                all:       @js($exAll),
                                not_found: @js($exNotFound),
                            },
                            lblTouched: {
                                create:    {{ $exCreate   !== '' ? 'true' : 'false' }},
                                all:       {{ $exAll      !== '' ? 'true' : 'false' }},
                                not_found: {{ $exNotFound !== '' ? 'true' : 'false' }},
                            },
                            generate() {
                                if (!this.slugTouched) {
                                    this.slug = this.singular.toLowerCase()
                                        .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
                                }
                                if (!this.lblTouched.create)    this.lbl.create    = 'Add New ' + this.singular;
                                if (!this.lblTouched.all)       this.lbl.all       = 'All ' + this.plural;
                                if (!this.lblTouched.not_found) this.lbl.not_found = 'No ' + this.plural + ' found';
                            }
                         }"
                         x-init="generate()"
                         class="space-y-4">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Singular name
                                    @if($isDefault)<span class="text-red-500">*</span>@endif
                                </label>
                                <input type="text"
                                       name="translations[{{ $lang->id }}][singular]"
                                       x-model="singular"
                                       @input="generate()"
                                       placeholder="e.g. Topic"
                                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       {{ $isDefault ? 'required' : '' }}>
                                @error("translations.{$lang->id}.singular")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Plural name
                                    @if($isDefault)<span class="text-red-500">*</span>@endif
                                </label>
                                <input type="text"
                                       name="translations[{{ $lang->id }}][plural]"
                                       x-model="plural"
                                       @input="generate()"
                                       placeholder="e.g. Topics"
                                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       {{ $isDefault ? 'required' : '' }}>
                                @error("translations.{$lang->id}.plural")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                URL slug
                                @if($isDefault)<span class="text-red-500">*</span>@endif
                            </label>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-2 rounded-l border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm select-none">/</span>
                                <input type="text"
                                       name="translations[{{ $lang->id }}][slug]"
                                       x-model="slug"
                                       @input="slugTouched = true"
                                       placeholder="topics"
                                       class="flex-1 rounded-r border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       {{ $isDefault ? 'required' : '' }}>
                            </div>
                            @error("translations.{$lang->id}.slug")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- More labels toggle --}}
                        <div>
                            <button type="button"
                                    @click="showMore = !showMore"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-400 hover:text-gray-700 transition-colors">
                                <svg class="w-3.5 h-3.5 transition-transform" :class="showMore ? 'rotate-90' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span x-text="showMore ? 'Hide extra labels' : 'More labels…'"></span>
                            </button>

                            <div x-show="showMore" x-cloak class="mt-3 grid grid-cols-2 gap-3 p-4 bg-gray-50 rounded border border-gray-200">
                                <p class="col-span-2 text-xs text-gray-400 -mt-1 mb-1">These labels are auto-generated. Override only if needed.</p>

                                @php
                                    $extraLabels = [
                                        'create'    => 'Add new (button)',
                                        'all'       => 'All items (heading)',
                                        'not_found' => 'Empty state text',
                                    ];
                                @endphp

                                @foreach($extraLabels as $key => $labelName)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ $labelName }}</label>
                                    <input type="text"
                                           name="translations[{{ $lang->id }}][labels][{{ $key }}]"
                                           x-model="lbl.{{ $key }}"
                                           @input="lblTouched.{{ $key }} = true"
                                           class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Options (shared) --}}
        <div class="bg-white border border-gray-200 rounded-md overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-800">Options</h2>
            </div>
            <div class="px-5 py-5">
                <label class="flex items-start gap-3 p-3 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                    <input type="checkbox"
                           name="is_hierarchical"
                           value="1"
                           {{ old('is_hierarchical', $taxonomy?->is_hierarchical) ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Hierarchical</p>
                        <p class="text-xs text-gray-400 mt-0.5">Terms can have parent/child relationships (like categories). Leave unchecked for flat tags.</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-md transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $taxonomy ? 'Save Changes' : 'Create Taxonomy' }}
            </button>
            <a href="{{ route('cms.admin.content-types.index') }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-md hover:bg-gray-100 transition-colors">
                Cancel
            </a>
        </div>

    </form>
</div>

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@endsection
