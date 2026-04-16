{{--
 | Contensio - The open content platform for Laravel.
 | Admin — blocks form.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@php
    $isNew      = ! collect($content->blocks ?? [])->firstWhere('id', $block['id'] ?? null);
    $typeName   = $content->contentType?->name ?? 'page';
    $typeLabel  = ucfirst($typeName);
    $blockName  = $blockConfig['label'] ?? ucfirst($block['type'] ?? '');
    $pageTitle  = ($isNew ? 'Add' : 'Edit') . ' ' . $blockName . ' Block';
    $formAction = $isNew
        ? route('contensio.account.blocks.store', $content->id)
        : route('contensio.account.blocks.update', [$content->id, $block['id']]);
    $backRoute  = $typeName === 'post'
        ? route('contensio.account.posts.edit', $content->id)
        : route('contensio.account.pages.edit', $content->id);

    $blockData         = $block['data'] ?? [];
    $blockSettings     = $block['settings'] ?? [];
    $blockTranslations = $block['translations'] ?? [];
    $fields            = $blockConfig['fields'] ?? [];

    // Separate field groups
    // Translatable fields (including translatable repeaters) go in language tabs
    $translatableFields       = [];
    $nonTranslatableFields    = [];
    $nonTranslatableRepeaters = [];

    foreach ($fields as $fieldName => $fieldDef) {
        if ($fieldDef['translatable'] ?? false) {
            $translatableFields[$fieldName] = $fieldDef;
        } elseif (($fieldDef['type'] ?? '') === 'repeater') {
            $nonTranslatableRepeaters[$fieldName] = $fieldDef;
        } else {
            $nonTranslatableFields[$fieldName] = $fieldDef;
        }
    }

    $hasRichtext   = collect($fields)->contains(fn ($f) => ($f['type'] ?? '') === 'richtext');
    $hasCode       = collect($fields)->contains(fn ($f) => ($f['type'] ?? '') === 'code');
    $multiLang     = $languages->count() > 1;
    $defaultLangId = $defaultLanguage?->id ?? $languages->first()?->id ?? 1;
@endphp

@section('title', $pageTitle)

@section('breadcrumb')
    <a href="{{ $backRoute }}" class="text-gray-500 hover:text-gray-700">{{ $typeLabel }}</a>
    <span class="mx-1.5 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">{{ $pageTitle }}</span>
@endsection

@push('styles')
<style>
[x-cloak] { display: none !important; }

.block-richtext-editor {
    min-height: 180px;
    border: 1px solid #d1d5db;
    border-radius: 0 0 0.5rem 0.5rem;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    line-height: 1.7;
    outline: none;
}
.block-richtext-editor:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.2); }
.block-richtext-editor p  { margin: 0 0 0.75em; }
.block-richtext-editor h2 { font-size: 1.25rem; font-weight: 700; margin: 1em 0 0.5em; }
.block-richtext-editor h3 { font-size: 1.1rem;  font-weight: 600; margin: 1em 0 0.5em; }
.block-richtext-editor ul { list-style: disc;    padding-left: 1.5em; margin: 0.5em 0; }
.block-richtext-editor ol { list-style: decimal; padding-left: 1.5em; margin: 0.5em 0; }
.block-richtext-editor blockquote { border-left: 3px solid #e5e7eb; padding-left: 1em; color: #6b7280; }
.block-richtext-editor code { background: #f3f4f6; padding: .1em .4em; border-radius: .25rem; font-size: .85em; }
.block-richtext-editor a   { color: #2563eb; text-decoration: underline; }

.richtext-toolbar { border: 1px solid #d1d5db; border-bottom: none; border-radius: 0.5rem 0.5rem 0 0; background: #f9fafb; padding: 0.25rem; display: flex; align-items: center; flex-wrap: wrap; gap: 2px; }
.richtext-toolbar button { padding: .25rem .45rem; border-radius: .375rem; color: #374151; font-size: .75rem; font-weight: 600; cursor: pointer; background: transparent; border: none; transition: background-color .1s; line-height: 1; }
.richtext-toolbar button:hover { background: #e5e7eb; }
.richtext-toolbar .sep { width: 1px; background: #e5e7eb; height: 1.25rem; margin: 0 2px; align-self: center; }

.code-editor-wrap { background: #1e1e2e; border-radius: .5rem; overflow: hidden; }
.code-editor-wrap textarea { width: 100%; min-height: 160px; padding: 1rem; font-family: monospace; font-size: .85rem; line-height: 1.6; color: #cdd6f4; background: transparent; border: none; outline: none; resize: vertical; }
</style>
@endpush

@section('content')

<form method="POST" action="{{ $formAction }}" id="block-form" novalidate>
@csrf
@if (! $isNew) @method('PUT') @endif

<input type="hidden" name="block_type" value="{{ $block['type'] }}">
<input type="hidden" name="block_id"   value="{{ $block['id'] }}">
<input type="hidden" name="is_active"  value="{{ ($block['is_active'] ?? true) ? '1' : '0' }}">
<input type="hidden" name="_stay"      value="0" id="input-stay">

<div class="flex items-start gap-6">

    {{-- ── Main fields ──────────────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 space-y-5">

        {{-- Non-translatable settings --}}
        @if (! empty($nonTranslatableFields))
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-800">Settings</h2>
            </div>
            <div class="p-5 grid grid-cols-2 gap-4">
                @foreach ($nonTranslatableFields as $fieldName => $fieldDef)
                    @php
                        $colSpan = ($fieldDef['width'] ?? '') === 'half' ? '' : 'col-span-2';
                        $value   = $blockData[$fieldName] ?? ($fieldDef['default'] ?? null);
                    @endphp
                    <div class="{{ $colSpan }}">
                        @include('contensio::admin.blocks.partials.field', [
                            'fieldName'  => $fieldName,
                            'fieldDef'   => $fieldDef,
                            'namePrefix' => 'block_data',
                            'value'      => $value,
                        ])
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Translatable fields — with language tabs --}}
        @if (! empty($translatableFields))
        <div class="bg-white rounded-xl border border-gray-200"
             x-data="{ activeLang: {{ $defaultLangId }} }">

            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-3">
                <h2 class="text-base font-bold text-gray-800">Content</h2>

                @if($multiLang)
                {{-- Language tab pills --}}
                <div class="flex items-center gap-1 ml-auto">
                    @foreach($languages as $lang)
                    <button type="button"
                            @click="activeLang = {{ $lang->id }}"
                            :class="activeLang === {{ $lang->id }}
                                ? 'bg-slate-800 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium transition-colors">
                        <span>{{ strtoupper($lang->code) }}</span>
                        @if($lang->is_default)
                        <span class="opacity-50 text-[10px]">★</span>
                        @endif
                        @if($lang->status === 'inactive')
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block" title="Inactive — admin only"></span>
                        @endif
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="p-5">
                @foreach($languages as $lang)
                @php
                    $langId = $lang->id;
                    // String cast for JSON-decoded array keys
                    $langTrans = $blockTranslations[(string)$langId]
                        ?? $blockTranslations[$langId]
                        ?? [];
                @endphp
                <div x-show="activeLang === {{ $langId }}" x-cloak="{{ $multiLang ? '' : 'false' }}"
                     @if(!$multiLang) @endif
                     class="space-y-4">
                    @foreach($translatableFields as $fieldName => $fieldDef)
                    @php
                        if (($fieldDef['type'] ?? '') === 'repeater') {
                            $value = $langTrans[$fieldName] ?? [];
                        } else {
                            // Fallback: if no translation yet, prefill from block_data for the default lang
                            $value = $langTrans[$fieldName]
                                ?? ($langId == $defaultLangId ? ($blockData[$fieldName] ?? null) : null);
                        }
                    @endphp

                    @if(($fieldDef['type'] ?? '') === 'repeater')
                        @include('contensio::admin.blocks.partials.repeater', [
                            'fieldName'  => $fieldName,
                            'fieldDef'   => $fieldDef,
                            'namePrefix' => "block_translations[{$langId}]",
                            'items'      => $value,
                        ])
                    @else
                        @include('contensio::admin.blocks.partials.field', [
                            'fieldName'  => $fieldName,
                            'fieldDef'   => $fieldDef,
                            'namePrefix' => "block_translations[{$langId}]",
                            'value'      => $value,
                        ])
                    @endif
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Non-translatable repeaters --}}
        @foreach ($nonTranslatableRepeaters as $fieldName => $fieldDef)
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-800">{{ $fieldDef['label'] ?? ucfirst($fieldName) }}</h2>
            </div>
            <div class="p-5">
                @include('contensio::admin.blocks.partials.repeater', [
                    'fieldName'  => $fieldName,
                    'fieldDef'   => $fieldDef,
                    'namePrefix' => 'block_data',
                    'items'      => $blockData[$fieldName] ?? [],
                ])
            </div>
        </div>
        @endforeach

    </div>

    {{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
    <div class="w-56 shrink-0 space-y-3">

        <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-2">
            <button type="submit"
                    onclick="document.getElementById('input-stay').value='0'"
                    class="w-full bg-ember-500 hover:bg-ember-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                {{ $isNew ? 'Add Block' : 'Save Block' }}
            </button>

            @if (! $isNew)
            <button type="submit"
                    onclick="document.getElementById('input-stay').value='1'"
                    class="w-full border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Save &amp; Stay
            </button>
            @endif

            <a href="{{ $backRoute }}"
               class="block text-center border border-gray-200 hover:bg-gray-50 text-gray-500 text-sm px-4 py-2 rounded-lg transition-colors">
                Cancel
            </a>
        </div>

        {{-- Block info --}}
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 text-xs text-gray-500 space-y-1.5">
            <div class="font-semibold text-gray-700">{{ $blockName }}</div>
            @if (! empty($blockConfig['description']))
            <p class="leading-relaxed">{{ $blockConfig['description'] }}</p>
            @endif
        </div>

        {{-- Language legend (multi-lang only) --}}
        @if($multiLang)
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4 space-y-2 text-xs text-gray-500">
            <p class="font-semibold text-gray-600 mb-1">Languages</p>
            @foreach($languages as $lang)
            <div class="flex items-center gap-2">
                <span class="font-mono font-medium text-gray-700">{{ strtoupper($lang->code) }}</span>
                <span class="text-gray-400">{{ $lang->name }}</span>
                @if($lang->is_default)
                <span class="ml-auto text-[10px] text-blue-500">default</span>
                @elseif($lang->status === 'inactive')
                <span class="ml-auto inline-flex items-center gap-1 text-amber-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>
                    admin only
                </span>
                @endif
            </div>
            @endforeach
        </div>
        @endif

    </div>

</div>

</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Richtext editor init — runs for all instances (multiple languages)
    document.querySelectorAll('.block-richtext-container').forEach(function (container) {
        const textarea = container.querySelector('textarea');
        const editorEl = container.querySelector('.block-richtext-editor');
        if (!textarea || !editorEl) return;

        editorEl.innerHTML = textarea.value;

        editorEl.addEventListener('input', () => { textarea.value = editorEl.innerHTML; });

        container.querySelectorAll('[data-cmd]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const cmd = this.dataset.cmd;
                const arg = this.dataset.arg || null;
                editorEl.focus();
                if (cmd === 'createLink') {
                    const url = prompt('Enter URL:');
                    if (url) document.execCommand(cmd, false, url);
                } else {
                    document.execCommand(cmd, false, arg);
                }
                textarea.value = editorEl.innerHTML;
            });
        });
    });
});
</script>
@endpush
