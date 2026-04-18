{{--
 | Contensio - The open content platform for Laravel.
 | Admin — terms form.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@php
    $txTrans  = $taxonomy->translations->first();
    $txPlural = $txTrans?->labels['plural'] ?? $taxonomy->name;

    $defaultLangId = $defaultLanguage?->id ?? $languages->first()?->id ?? 1;
    $multiLang     = $languages->count() > 1;
@endphp

@section('title', $term ? 'Edit Term' : 'New Term')

@section('breadcrumb')
    <a href="{{ route('contensio.account.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('contensio.account.content-types.index') }}" class="text-gray-500 hover:text-gray-700">Content Types</a>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('contensio.account.terms.index', $taxonomy->id) }}" class="text-gray-500 hover:text-gray-700">{{ $txPlural }}</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">{{ $term ? 'Edit Term' : 'New Term' }}</span>
@endsection

@section('content')

<div class="max-w-xl">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">{{ $term ? 'Edit Term' : 'New Term' }}</h1>
        <p class="text-sm text-gray-400 mt-0.5">
            Taxonomy: <span class="font-semibold text-gray-600">{{ $txPlural }}</span>
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
          action="{{ $term
              ? route('contensio.account.terms.update', [$taxonomy->id, $term->id])
              : route('contensio.account.terms.store', $taxonomy->id) }}">
        @csrf
        @if($term) @method('PUT') @endif

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

            <div class="px-5 py-5 space-y-0">
                @foreach($languages as $lang)
                @php
                    $defName = old("translations.{$lang->id}.name", $existing[$lang->id]['name'] ?? '');
                    $defSlug = old("translations.{$lang->id}.slug", $existing[$lang->id]['slug'] ?? '');
                    $defDesc = old("translations.{$lang->id}.description", $existing[$lang->id]['description'] ?? '');
                    $slugTouched = ($defSlug !== '') ? 'true' : 'false';
                    $isDefault   = $lang->id === $defaultLangId;
                @endphp
                <div x-show="activeLang === {{ $lang->id }}" x-cloak="{{ $multiLang ? '' : 'false' }}">
                    <div x-data="{
                            name: @js($defName),
                            slug: @js($defSlug),
                            slugTouched: {{ $slugTouched }},
                            generate() {
                                if (this.slugTouched) return;
                                this.slug = this.name.toLowerCase()
                                    .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
                            }
                         }"
                         x-init="generate()"
                         class="space-y-4">

                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Name @if($isDefault)<span class="text-red-500">*</span>@endif
                            </label>
                            <input type="text"
                                   name="translations[{{ $lang->id }}][name]"
                                   x-model="name"
                                   @input="generate()"
                                   placeholder="e.g. Technology"
                                   class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent"
                                   {{ $isDefault ? 'required' : '' }}>
                            @error("translations.{$lang->id}.name")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                URL slug @if($isDefault)<span class="text-red-500">*</span>@endif
                            </label>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-2 rounded-l border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm select-none">/</span>
                                <input type="text"
                                       name="translations[{{ $lang->id }}][slug]"
                                       x-model="slug"
                                       @input="slugTouched = true"
                                       placeholder="technology"
                                       class="flex-1 rounded-r border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent"
                                       {{ $isDefault ? 'required' : '' }}>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Auto-generated from name. Lowercase letters, numbers and hyphens only.</p>
                            @error("translations.{$lang->id}.slug")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Description <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <textarea name="translations[{{ $lang->id }}][description]"
                                      rows="3"
                                      placeholder="Short description of this term…"
                                      class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent resize-none">{{ $defDesc }}</textarea>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Image --}}
        <div class="bg-white border border-gray-200 rounded-md overflow-hidden mb-4"
             x-data="{ imgUrl: @js($term?->image?->url() ?? null) }"
             x-on:cms:media-selected.window="if ($event.detail.inputName === 'image_id' && $event.detail.items[0]) imgUrl = $event.detail.items[0].url">

            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-800">Image <span class="text-gray-400 font-normal text-sm">(optional)</span></h2>
            </div>

            <div class="px-5 py-5 space-y-3">
                <input type="hidden" name="image_id"
                       value="{{ old('image_id', $term?->image_id ?? '') }}">

                <div x-show="imgUrl" x-cloak
                     class="relative rounded-lg overflow-hidden border border-gray-200 bg-gray-50 aspect-video">
                    <img :src="imgUrl" alt="Term image" class="w-full h-full object-cover">
                    <button type="button"
                            @click="imgUrl = null; $el.closest('.bg-white').querySelector('[name=image_id]').value = ''"
                            class="absolute top-2 right-2 w-7 h-7 rounded-full bg-gray-900/60 hover:bg-red-500 text-white flex items-center justify-center transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="!imgUrl"
                     class="rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 py-8 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-xs text-gray-400">No image selected</p>
                </div>

                <button type="button"
                        @click="$dispatch('cms:media-pick', { inputName: 'image_id', accept: 'image/', multiple: false })"
                        class="w-full text-sm font-medium text-gray-700 border border-gray-300 hover:bg-gray-50 px-3 py-2 rounded-md transition-colors"
                        x-text="imgUrl ? 'Change Image' : 'Set Image'">
                </button>
            </div>
        </div>

        {{-- Parent (hierarchical only) --}}
        @if($taxonomy->is_hierarchical && ! empty($parentOptions))
        <div class="bg-white border border-gray-200 rounded-md overflow-hidden mb-4">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-800">Parent term</h2>
            </div>
            <div class="px-5 py-5">
                <select name="parent_id"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent bg-white">
                    <option value="">— None (top level) —</option>
                    @foreach($parentOptions as $parentId => $parentName)
                    <option value="{{ $parentId }}" {{ old('parent_id', $term?->parent_id) == $parentId ? 'selected' : '' }}>
                        {{ $parentName }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-md transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $term ? 'Save Changes' : 'Create Term' }}
            </button>
            <a href="{{ route('contensio.account.terms.index', $taxonomy->id) }}"
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
