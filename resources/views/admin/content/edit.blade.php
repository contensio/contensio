{{--
 | Contensio - The open content platform for Laravel.
 | Admin — content edit.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@php
    $isNew     = is_null($content);
    $typeName  = $type->name;
    $typeLabel = ucfirst($typeName);
    $pageTitle = $isNew ? "Add New {$typeLabel}" : "Edit {$typeLabel}";
    $blocks    = $content?->blocks ?? [];

    $blockCategories = [
        'text'     => 'Text',
        'media'    => 'Media',
        'layout'   => 'Layout',
        'advanced' => 'Advanced',
    ];
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <a href="{{ $backRoute }}" class="text-gray-500 hover:text-gray-700">{{ $typeLabel }}s</a>
    <span class="mx-1.5 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">
        {{ $isNew ? "Add New {$typeLabel}" : ($existing[$defaultLangId]['title'] ?? 'Edit') }}
    </span>
@endsection

@section('content')

@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-3">
    {{ session('success') }}
</div>
@endif

{{-- Autosave restore banner (only shown when a newer autosave exists for this user) --}}
@if(! $isNew && ! empty($autosave))
<div id="autosave-banner"
     x-data="contensioAutosaveBanner(@js($autosave['data']), @js(route('cms.admin.content.autosave.discard', $content->id)))"
     x-show="visible"
     x-cloak
     class="mb-4 flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-900 rounded-lg px-4 py-3">
    <svg class="w-5 h-5 shrink-0 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="flex-1 text-sm">
        <p class="font-semibold">You have unsaved changes from {{ $autosave['human'] }}.</p>
        <p class="text-amber-800/80 text-xs mt-0.5">Restore them, or discard and keep the last saved version.</p>
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <button type="button" @click="restore()"
                class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-3 py-1.5 rounded-lg transition-colors">
            Restore
        </button>
        <button type="button" @click="discard()"
                class="text-sm text-amber-700 hover:text-amber-900 font-medium px-2 py-1.5">
            Discard
        </button>
    </div>
</div>
@endif

{{-- Autosave status indicator (top-right fixed, fades in on activity) --}}
<div id="autosave-indicator"
     class="fixed bottom-6 right-6 z-40 hidden items-center gap-2 bg-white border border-gray-200 rounded-lg shadow-lg px-3 py-2 text-xs font-medium transition-opacity"></div>

<form method="POST" action="{{ $storeRoute }}" id="content-form">
@csrf
@if ($method === 'PUT') @method('PUT') @endif

<div class="flex items-start gap-6" x-data="{ activeLang: {{ $defaultLangId }} }">

    {{-- ── Main column ──────────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- Title + Slug --}}
        <div class="bg-white rounded-md border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                <h2 class="text-base font-bold text-gray-800 flex-1">Title</h2>

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

            <div class="p-5 space-y-0">
                @foreach($languages as $lang)
                @php
                    $defTitle    = old("translations.{$lang->id}.title", $existing[$lang->id]['title'] ?? '');
                    $defSlug     = old("translations.{$lang->id}.slug",  $existing[$lang->id]['slug']  ?? '');
                    $slugTouched = ($defSlug !== '') ? 'true' : 'false';
                    $isDefault   = $lang->id === $defaultLangId;
                @endphp
                <div x-show="activeLang === {{ $lang->id }}" x-cloak="{{ $multiLang ? '' : 'false' }}">
                    <div x-data="{
                            titleVal: @js($defTitle),
                            slug: @js($defSlug),
                            slugTouched: {{ $slugTouched }},
                            generate() {
                                if (this.slugTouched) return;
                                this.slug = this.titleVal.toLowerCase()
                                    .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
                            }
                         }"
                         class="space-y-3">

                        <div>
                            <input type="text"
                                   name="translations[{{ $lang->id }}][title]"
                                   x-model="titleVal"
                                   @input="generate()"
                                   placeholder="Enter {{ $typeName }} title"
                                   class="w-full text-lg font-medium border border-gray-300 rounded px-3.5 py-2.5
                                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   {{ $isDefault ? 'required autofocus' : '' }}>
                            @error("translations.{$lang->id}.title")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400 shrink-0">Slug:</span>
                            <span class="inline-flex items-center px-2.5 py-1.5 rounded-l border border-r-0 border-gray-200 bg-gray-50 text-gray-400 text-xs select-none">/</span>
                            <input type="text"
                                   name="translations[{{ $lang->id }}][slug]"
                                   x-model="slug"
                                   @input="slugTouched = true"
                                   placeholder="auto-generated"
                                   class="flex-1 text-xs border border-gray-200 rounded-r px-2.5 py-1.5
                                          focus:outline-none focus:ring-1 focus:ring-blue-500 text-gray-600 font-mono">
                        </div>

                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Excerpt (if content type supports it) --}}
        @if($type->has_excerpt)
        <div class="bg-white rounded-md border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-800">Excerpt</h2>
            </div>
            <div class="p-5 space-y-0">
                @foreach($languages as $lang)
                @php $defExcerpt = old("translations.{$lang->id}.excerpt", $existing[$lang->id]['excerpt'] ?? ''); @endphp
                <div x-show="activeLang === {{ $lang->id }}" x-cloak="{{ $multiLang ? '' : 'false' }}">
                    <textarea name="translations[{{ $lang->id }}][excerpt]"
                              rows="3"
                              placeholder="Short summary shown in listings…"
                              class="w-full rounded border border-gray-300 px-3 py-2 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                     resize-none text-gray-700">{{ $defExcerpt }}</textarea>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Block editor --}}
        <div class="bg-white rounded-md border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-800">Content Blocks</h2>

                @if (! $isNew)
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white
                                   text-sm font-medium px-3 py-1.5 rounded-md transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Block
                    </button>

                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-1 w-72 bg-white rounded-md shadow-lg border border-gray-200
                                py-2 z-50 max-h-96 overflow-y-auto">
                        @php $grouped = $blockTypes->groupBy('category'); @endphp
                        @foreach ($blockCategories as $catKey => $catLabel)
                            @if ($grouped->has($catKey))
                            <div class="px-3 pt-2 pb-1 text-xs text-gray-400 uppercase tracking-wider font-medium
                                        {{ ! $loop->first ? 'mt-1 border-t border-gray-100' : '' }}">
                                {{ $catLabel }}
                            </div>
                            @foreach ($grouped[$catKey] as $bt)
                            <a href="{{ route('cms.admin.blocks.new', [$content->id, $bt->name]) }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                @include('cms::admin.content.partials.block-icon', ['icon' => $bt->icon])
                                <div>
                                    <div class="font-medium">{{ $bt->label }}</div>
                                    @if ($bt->description)
                                    <div class="text-xs text-gray-400 leading-tight">{{ $bt->description }}</div>
                                    @endif
                                </div>
                            </a>
                            @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            @if ($isNew)
            <div class="px-5 py-12 text-center text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-sm font-medium text-gray-500 mb-1">Save first, then add blocks</p>
                <p class="text-xs text-gray-400">Create the {{ $typeName }} to start adding content blocks.</p>
            </div>
            @elseif (empty($blocks))
            <div class="px-5 py-12 text-center" id="block-list">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-sm font-medium text-gray-500 mb-1">No blocks yet</p>
                <p class="text-xs text-gray-400">Click "Add Block" to start building your {{ $typeName }}.</p>
            </div>
            @else
            <div id="block-list" class="divide-y divide-gray-100">
                @foreach ($blocks as $block)
                @php
                    $bType   = $block['type'] ?? 'unknown';
                    $bConfig = config("cms.blocks.{$bType}", []);
                    $bName   = $bConfig['label'] ?? ucfirst($bType);
                    $bIcon   = $bConfig['icon'] ?? 'code-bracket';
                    $bActive = $block['is_active'] ?? true;
                    $bId     = $block['id'] ?? '';

                    $bData   = $block['data'] ?? [];
                    $bTrans  = $block['translations'] ?? [];
                    $preview = '';
                    $pData   = $bTrans ? (reset($bTrans) ?: $bData) : $bData;
                    if (!empty($pData['title']))   $preview = $pData['title'];
                    elseif (!empty($pData['heading'])) $preview = $pData['heading'];
                    elseif (!empty($pData['text']))    $preview = $pData['text'];
                    elseif (!empty($pData['content'])) $preview = \Illuminate\Support\Str::limit(strip_tags($pData['content']), 60);
                    elseif (!empty($pData['quote']))   $preview = \Illuminate\Support\Str::limit($pData['quote'], 60);
                    elseif (!empty($pData['message'])) $preview = \Illuminate\Support\Str::limit($pData['message'], 60);
                    elseif (!empty($pData['url']))     $preview = $pData['url'];
                @endphp

                <div class="flex items-center gap-3 px-5 py-3.5 {{ ! $bActive ? 'opacity-50' : '' }}"
                     data-block-id="{{ $bId }}">

                    <div class="block-drag-handle cursor-grab text-gray-300 hover:text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                        </svg>
                    </div>

                    <div class="w-8 h-8 bg-gray-100 rounded-md flex items-center justify-center shrink-0">
                        @include('cms::admin.content.partials.block-icon', ['icon' => $bIcon, 'class' => 'w-4 h-4 text-gray-500'])
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 flex items-center gap-2">
                            {{ $bName }}
                            @if (! $bActive)
                            <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">Hidden</span>
                            @endif
                        </div>
                        @if ($preview)
                        <div class="text-xs text-gray-400 truncate mt-0.5">{{ $preview }}</div>
                        @endif
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        <a href="{{ route('cms.admin.blocks.edit', [$content->id, $bId]) }}"
                           class="p-1.5 rounded-md text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                           title="Edit block">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </a>

                        <button type="button"
                                class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors block-toggle-btn"
                                data-block-id="{{ $bId }}"
                                data-toggle-url="{{ route('cms.admin.blocks.toggle', [$content->id, $bId]) }}"
                                title="{{ $bActive ? 'Hide block' : 'Show block' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if ($bActive)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                @endif
                            </svg>
                        </button>

                        <form id="delete-block-{{ $bId }}"
                              method="POST"
                              action="{{ route('cms.admin.blocks.destroy', [$content->id, $bId]) }}"
                              class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button"
                                @click="$dispatch('cms:confirm', {
                                    title: 'Delete Block',
                                    description: 'Delete this {{ $bName }} block? This cannot be undone.',
                                    confirmLabel: 'Delete Block',
                                    formId: 'delete-block-{{ $bId }}'
                                })"
                                class="p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                title="Delete block">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

    {{-- ── Sidebar ────────────────────────────────────────────────────────── --}}
    <div class="w-80 shrink-0 space-y-4">

        {{-- Publish --}}
        <div class="bg-white rounded-md border border-gray-200 p-4">
            <h3 class="text-base font-bold text-gray-800 mb-3">Publish</h3>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1.5">Status</label>
                <select name="status"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="draft"     {{ old('status', $content?->status ?? 'draft') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $content?->status ?? 'draft') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>

            <div class="flex flex-col gap-2">
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-md transition-colors">
                    {{ $isNew ? "Create {$typeLabel}" : 'Save Changes' }}
                </button>

                @if (! $isNew)
                @php
                    $deleteAction = match($typeName) {
                        'page'  => route('cms.admin.pages.destroy', $content->id),
                        'post'  => route('cms.admin.posts.destroy', $content->id),
                        default => route('cms.admin.content.destroy', [$typeName, $content->id]),
                    };
                @endphp
                <form id="delete-content-{{ $content->id }}"
                      method="POST"
                      action="{{ $deleteAction }}"
                      class="hidden">
                    @csrf @method('DELETE')
                </form>
                <button type="button"
                        @click="$dispatch('cms:confirm', {
                            title: 'Delete {{ $typeLabel }}',
                            description: 'Are you sure? All blocks and content will be permanently removed.',
                            confirmLabel: 'Delete {{ $typeLabel }}',
                            formId: 'delete-content-{{ $content->id }}'
                        })"
                        class="w-full border border-red-200 text-red-600 hover:bg-red-50 text-sm font-medium
                               px-4 py-2 rounded-md transition-colors">
                    Delete {{ $typeLabel }}
                </button>
                @endif
            </div>
        </div>

        {{-- Taxonomy term selection --}}
        @foreach($taxonomies as $taxonomy)
        @php
            $txTrans  = $taxonomy->translations->first();
            $txPlural = $txTrans?->labels['plural']   ?? $taxonomy->name;
            $txSing   = $txTrans?->labels['singular'] ?? $taxonomy->name;
        @endphp

        @if($taxonomy->is_hierarchical)
        {{-- Hierarchical: checkboxes with parent/child indentation --}}
        @if($taxonomy->terms->isNotEmpty())
        <div class="bg-white rounded-md border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-800">{{ $txPlural }}</h3>
            </div>
            <div class="px-4 py-3 max-h-52 overflow-y-auto space-y-1.5">
                @foreach($taxonomy->terms->whereNull('parent_id')->sortBy('position') as $term)
                @php $tTrans = $term->translations->firstWhere('language_id', $defaultLangId) ?? $term->translations->first(); @endphp
                <label class="flex items-center gap-2.5 cursor-pointer group">
                    <input type="checkbox" name="term_ids[]" value="{{ $term->id }}"
                           {{ in_array($term->id, $selectedTermIds) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $tTrans?->name ?? '—' }}</span>
                </label>
                @foreach($taxonomy->terms->where('parent_id', $term->id)->sortBy('position') as $child)
                @php $cTrans = $child->translations->firstWhere('language_id', $defaultLangId) ?? $child->translations->first(); @endphp
                <label class="flex items-center gap-2.5 cursor-pointer group pl-5">
                    <input type="checkbox" name="term_ids[]" value="{{ $child->id }}"
                           {{ in_array($child->id, $selectedTermIds) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-500 group-hover:text-gray-700">{{ $cTrans?->name ?? '—' }}</span>
                </label>
                @endforeach
                @endforeach
            </div>
        </div>
        @endif

        @else
        {{-- Flat: Tagify tag input (allows selecting existing + creating new) --}}
        @php
            $tagifyWhitelist = $taxonomy->terms->map(function ($term) use ($defaultLangId) {
                $tr = $term->translations->firstWhere('language_id', $defaultLangId)
                    ?? $term->translations->first();
                return ['value' => $tr?->name ?? '', 'id' => $term->id];
            })->filter(fn ($t) => $t['value'] !== '')->values();

            $tagifyValue = $taxonomy->terms->whereIn('id', $selectedTermIds)->map(function ($term) use ($defaultLangId) {
                $tr = $term->translations->firstWhere('language_id', $defaultLangId)
                    ?? $term->translations->first();
                return ['value' => $tr?->name ?? '', 'id' => $term->id];
            })->filter(fn ($t) => $t['value'] !== '')->values();
        @endphp
        <div class="bg-white rounded-md border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-800">{{ $txPlural }}</h3>
            </div>
            <div class="px-4 py-3">
                <input class="cms-tagify"
                       name="tagify[{{ $taxonomy->id }}]"
                       data-whitelist="{{ $tagifyWhitelist->toJson() }}"
                       data-placeholder="Add {{ strtolower($txSing) }}…"
                       value="{{ $tagifyValue->isNotEmpty() ? $tagifyValue->toJson() : '' }}">
                <p class="mt-1.5 text-xs text-gray-400">Type to search or create a new {{ strtolower($txSing) }}.</p>
            </div>
        </div>
        @endif

        @endforeach

        {{-- Custom Fields (from attached groups) --}}
        @if(isset($fieldGroups) && $fieldGroups->isNotEmpty())
        @foreach($fieldGroups as $group)
        @php
            if ($group->fields->isEmpty()) continue;
            $groupTrans = $group->translations->firstWhere('language_id', $defaultLangId) ?? $group->translations->first();
            $groupLabel = $groupTrans?->label ?? $group->label;

            // Group fields by section (preserving order; null section first)
            $bySection = [];
            foreach ($group->fields as $field) {
                $bySection[$field->section ?? ''][] = $field;
            }
        @endphp
        <div class="bg-white rounded-md border border-gray-200" x-data="{ cfOpen: true }">
            <button type="button" @click="cfOpen = !cfOpen"
                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition-colors rounded-md">
                <h3 class="text-base font-bold text-gray-800">{{ $groupLabel }}</h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-150 shrink-0"
                     :class="cfOpen ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="cfOpen" class="border-t border-gray-100 px-4 py-4 space-y-5">

                @foreach($bySection as $sectionName => $sectionFields)
                    @if($sectionName !== '')
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider pt-2 border-t border-gray-100 first:border-0 first:pt-0">{{ $sectionName }}</p>
                    @endif

                    @foreach($sectionFields as $field)
                        @php
                            $fTrans = $field->translations->firstWhere('language_id', $defaultLangId) ?? $field->translations->first();
                            $fLabel = $fTrans?->label ?? $field->key;
                            $fHelp  = $fTrans?->help_text;
                            $fPh    = $fTrans?->placeholder;
                            $cfg    = $field->config ?? [];
                        @endphp

                        @if($field->is_translatable)
                            @foreach($languages as $lang)
                            @php
                                $valKey  = $field->id . ':' . $lang->id;
                                $current = old("fields.{$field->id}.{$lang->id}", $fieldValues[$valKey] ?? '');
                            @endphp
                            <div x-show="activeLang === {{ $lang->id }}">
                                @include('cms::admin.content.partials.field-input', [
                                    'field'   => $field,
                                    'inputName' => "fields[{$field->id}][{$lang->id}]",
                                    'label'   => $fLabel . ' (' . $lang->code . ')',
                                    'help'    => $fHelp,
                                    'placeholder' => $fPh,
                                    'cfg'     => $cfg,
                                    'current' => $current,
                                ])
                            </div>
                            @endforeach
                        @else
                            @php
                                $valKey  = $field->id . ':_';
                                $current = old("fields.{$field->id}", $fieldValues[$valKey] ?? '');
                            @endphp
                            @include('cms::admin.content.partials.field-input', [
                                'field'   => $field,
                                'inputName' => "fields[{$field->id}]",
                                'label'   => $fLabel,
                                'help'    => $fHelp,
                                'placeholder' => $fPh,
                                'cfg'     => $cfg,
                                'current' => $current,
                            ])
                        @endif
                    @endforeach
                @endforeach

            </div>
        </div>
        @endforeach
        @endif

        {{-- SEO --}}
        <div class="bg-white rounded-md border border-gray-200" x-data="{ seoOpen: false }">
            <button type="button"
                    @click="seoOpen = !seoOpen"
                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition-colors rounded-md">
                <h3 class="text-base font-bold text-gray-800">SEO</h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-150 shrink-0"
                     :class="seoOpen ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="seoOpen" class="border-t border-gray-100">
                @foreach($languages as $lang)
                @php
                    $defMetaTitle = old("translations.{$lang->id}.meta_title", $existing[$lang->id]['meta_title'] ?? '');
                    $defMetaDesc  = old("translations.{$lang->id}.meta_description", $existing[$lang->id]['meta_description'] ?? '');
                @endphp
                <div x-show="activeLang === {{ $lang->id }}" class="px-4 py-3 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">
                            Meta title <span class="text-gray-400 font-normal">≤ 60 chars</span>
                        </label>
                        <input type="text"
                               name="translations[{{ $lang->id }}][meta_title]"
                               value="{{ $defMetaTitle }}"
                               maxlength="120"
                               placeholder="Leave blank to use page title"
                               class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">
                            Meta description <span class="text-gray-400 font-normal">≤ 160 chars</span>
                        </label>
                        <textarea name="translations[{{ $lang->id }}][meta_description]"
                                  rows="3"
                                  maxlength="300"
                                  placeholder="Brief description for search results"
                                  class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none">{{ $defMetaDesc }}</textarea>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Info --}}
        @if (! $isNew)
        <div class="bg-white rounded-md border border-gray-200 p-4 text-sm text-gray-500 space-y-1.5">
            <div class="flex justify-between">
                <span>Author</span>
                <span class="text-gray-700 font-medium">{{ $content->author?->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Created</span>
                <span class="text-gray-700">{{ $content->created_at->format('M d, Y') }}</span>
            </div>
            @if ($content->published_at)
            <div class="flex justify-between">
                <span>Published</span>
                <span class="text-gray-700">{{ $content->published_at->format('M d, Y') }}</span>
            </div>
            @endif
            <div class="flex justify-between">
                <span>Blocks</span>
                <span class="text-gray-700">{{ count($content->blocks ?? []) }}</span>
            </div>
        </div>
        @endif

    </div>

</div>

</form>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
<style>
[x-cloak] { display: none !important; }

/* Tagify — match admin theme */
.tagify {
    --tag-bg: #f1f5f9;
    --tag-hover: #e2e8f0;
    --tag-text-color: #1e293b;
    --tag-border-color: transparent;
    --tag-remove-btn-color: #94a3b8;
    --tag-remove-btn-bg--hover: #fca5a5;
    --tags-border-color: #d1d5db;
    --tags-hover-border-color: #9ca3af;
    --tags-focus-border-color: #3b82f6;
    --input-color: #374151;
    --placeholder-color: #9ca3af;
    --placeholder-color-focus: #d1d5db;
    --tag-pad: 0.2em 0.45em;
    border-radius: 0.375rem;
    padding: 0.25rem;
    min-height: 2.25rem;
    font-size: 0.875rem;
    box-shadow: none;
}
.tagify:hover   { --tags-border-color: #9ca3af; }
.tagify--focus  { --tags-border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.2); }
.tagify__tag    { border-radius: 0.25rem; margin: 2px; }
.tagify__tag > div { padding: 0.2em 0.4em; }
.tagify__tag__removeBtn { border-radius: 0 0.25rem 0.25rem 0; }
.tagify__input  { padding: 0.2em 0.4em; }
.tagify__dropdown { border-radius: 0.375rem; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,.08); font-size: 0.875rem; }
.tagify__dropdown__item { padding: 0.45rem 0.75rem; }
.tagify__dropdown__item--active { background: #eff6ff; color: #1d4ed8; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Tagify — flat taxonomy tag inputs ────────────────────────────────────
    document.querySelectorAll('.cms-tagify').forEach(function (input) {
        const whitelist    = JSON.parse(input.dataset.whitelist || '[]');
        const placeholder  = input.dataset.placeholder || 'Add tag…';

        const tagify = new Tagify(input, {
            whitelist:        whitelist,
            enforceWhitelist: false,         // allow creating new tags
            dropdown: {
                enabled:       0,            // show suggestions from 0 chars
                maxItems:      30,
                closeOnSelect: false,
                highlightFirst: true,
            },
            // Store only value + id so controller can parse cleanly
            originalInputValueFormat: function (tags) {
                return JSON.stringify(tags.map(function (t) {
                    return { value: t.value, id: t.data?.id ?? '' };
                }));
            },
        });

        // Keyboard UX: open dropdown on focus
        input.addEventListener('focus', function () { tagify.dropdown.show(); }, true);
    });


    // ── Block drag-and-drop reorder ──────────────────────────────────────────
    const blockList = document.getElementById('block-list');
    @if (! $isNew && $content && count($blocks))
    if (blockList) {
        new Sortable(blockList, {
            handle: '.block-drag-handle',
            animation: 150,
            ghostClass: 'bg-blue-50',
            onEnd: function () {
                const order = Array.from(blockList.querySelectorAll('[data-block-id]'))
                    .map(el => el.dataset.blockId)
                    .filter(Boolean);

                fetch('{{ route('cms.admin.blocks.reorder', $content->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ order }),
                });
            }
        });
    }
    @endif

    // ── Block toggle (show/hide) ─────────────────────────────────────────────
    document.querySelectorAll('.block-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            fetch(this.dataset.toggleUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            }).then(() => window.location.reload());
        });
    });

});
</script>

@if(! $isNew)
{{-- Autosave: capture dirty form state every few seconds + on blur. --}}
<script>
    (function () {
        const form = document.getElementById('content-form');
        if (!form) return;

        const autosaveUrl = @js(route('cms.admin.content.autosave', $content->id));
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const indicator = document.getElementById('autosave-indicator');

        let lastSerialized = null;
        let debounceTimer  = null;
        let statusTimer    = null;

        const setStatus = (text, kind) => {
            if (!indicator) return;
            clearTimeout(statusTimer);
            indicator.classList.remove('hidden');
            indicator.classList.add('flex');
            indicator.style.opacity = '1';
            const color = kind === 'saving' ? 'text-gray-500'
                        : kind === 'saved'  ? 'text-green-700'
                        : kind === 'error'  ? 'text-red-700'
                        : 'text-gray-500';
            const dot   = kind === 'saving' ? 'bg-gray-300 animate-pulse'
                        : kind === 'saved'  ? 'bg-green-500'
                        : kind === 'error'  ? 'bg-red-500'
                        : 'bg-gray-300';
            indicator.innerHTML =
                '<span class="w-2 h-2 rounded-full ' + dot + '"></span>' +
                '<span class="' + color + '">' + text + '</span>';
            if (kind === 'saved') {
                statusTimer = setTimeout(() => {
                    indicator.style.opacity = '0';
                    setTimeout(() => indicator.classList.add('hidden'), 400);
                }, 3000);
            }
        };

        const serializeFD = () => {
            const fd = new FormData(form);
            fd.delete('_token');
            fd.delete('_method');
            return fd;
        };

        // Lightweight change-detection signature: flat string of key=value pairs
        const signature = (fd) => {
            const parts = [];
            for (const [k, v] of fd.entries()) {
                if (v instanceof File) continue; // skip file uploads from signature
                parts.push(k + '=' + v);
            }
            return parts.join('&');
        };

        const doAutosave = async () => {
            const fd = serializeFD();
            const sig = signature(fd);
            if (sig === lastSerialized) return; // no changes since last save

            setStatus('Saving…', 'saving');
            fd.set('_token', csrf);
            try {
                const res = await fetch(autosaveUrl, {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                lastSerialized = sig;
                setStatus('Draft saved', 'saved');
            } catch (err) {
                console.warn('Autosave failed:', err);
                setStatus('Couldn\'t autosave', 'error');
            }
        };

        const scheduleAutosave = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(doAutosave, 2500);
        };

        form.addEventListener('input', scheduleAutosave);
        form.addEventListener('change', scheduleAutosave);

        // Periodic safety save every 30 seconds even without events
        setInterval(doAutosave, 30_000);

        // Final save on unload (best-effort)
        window.addEventListener('beforeunload', () => {
            const fd = serializeFD();
            if (signature(fd) === lastSerialized) return;
            fd.set('_token', csrf);
            navigator.sendBeacon(autosaveUrl, fd);
        });

        // Snapshot the initial form state so the first change is detected
        lastSerialized = signature(serializeFD());
    })();

    // Alpine component for the restore banner
    window.contensioAutosaveBanner = (data, discardUrl) => ({
        visible: true,
        restore() {
            // Walk the saved data and set each matching form field
            const form = document.getElementById('content-form');
            if (!form) return;

            const apply = (name, value) => {
                const elements = form.querySelectorAll('[name="' + CSS.escape(name) + '"]');
                if (!elements.length) return;
                elements.forEach((el) => {
                    if (el.type === 'checkbox' || el.type === 'radio') {
                        el.checked = String(el.value) === String(value);
                    } else {
                        el.value = value ?? '';
                        // Trigger input event so Alpine x-model bindings update
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            };

            const flatten = (obj, prefix = '') => {
                if (obj === null || obj === undefined) return;
                if (typeof obj !== 'object') {
                    apply(prefix, obj);
                    return;
                }
                for (const [k, v] of Object.entries(obj)) {
                    const key = prefix ? `${prefix}[${k}]` : k;
                    if (v !== null && typeof v === 'object' && !Array.isArray(v)) {
                        flatten(v, key);
                    } else if (Array.isArray(v)) {
                        v.forEach((item, i) => {
                            if (typeof item === 'object' && item !== null) {
                                flatten(item, `${key}[${i}]`);
                            } else {
                                apply(`${key}[${i}]`, item);
                            }
                        });
                    } else {
                        apply(key, v);
                    }
                }
            };

            flatten(data);
            this.visible = false;
        },
        async discard() {
            try {
                await fetch(discardUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
            } catch {}
            this.visible = false;
        },
    });
</script>
@endif
@endpush

@endsection
