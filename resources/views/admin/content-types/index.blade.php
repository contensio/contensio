{{--
 | Contensio - The open content platform for Laravel.
 | Admin — content-types index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'Content Types')

@section('breadcrumb')
    <a href="{{ route('cms.admin.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">Content Types</span>
@endsection

@section('content')

@if (session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
    {{ session('error') }}
</div>
@endif
@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Content Types</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage post types and their taxonomies.</p>
    </div>
    <a href="{{ route('cms.admin.content-types.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Content Type
    </a>
</div>

<div class="space-y-4">
    @foreach($types as $type)
    @php
        $trans    = $type->translations->first();
        $singular = $trans?->labels['singular'] ?? $type->name;
        $plural   = $trans?->labels['plural']   ?? $type->name;
        $slug     = $trans?->slug ?? $type->name;
    @endphp

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        {{-- Type header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-200 bg-white">
            <div class="flex-1 flex items-center gap-3 min-w-0">
                @php $isEmoji = $type->icon && mb_strlen($type->icon) <= 4; @endphp
                @if($isEmoji)
                <span class="text-lg leading-none">{{ $type->icon }}</span>
                @else
                <div class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                @endif
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-900 text-sm">{{ $plural }}</span>
                        @if($type->is_system)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">Core</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $slug }}</p>
                </div>
            </div>

            {{-- Feature badges --}}
            <div class="hidden md:flex items-center gap-1.5 flex-wrap">
                @if($type->has_excerpt)
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-xs">Excerpt</span>
                @endif
                @if($type->has_featured_image)
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-xs">Featured image</span>
                @endif
                @if($type->has_categories)
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-xs">Categories</span>
                @endif
                @if($type->has_tags)
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-xs">Tags</span>
                @endif
                @if($type->is_hierarchical)
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-xs">Hierarchical</span>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('cms.admin.content-types.edit', $type->id) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-white transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Edit
                </a>

                @if(! $type->is_system)
                {{-- Hidden delete form --}}
                <form id="delete-type-{{ $type->id }}"
                      method="POST"
                      action="{{ route('cms.admin.content-types.destroy', $type->id) }}"
                      class="hidden">
                    @csrf @method('DELETE')
                </form>
                <button type="button"
                        @click="$dispatch('cms:confirm', {
                            title: 'Delete {{ addslashes($plural) }}?',
                            description: 'This will permanently delete the &quot;{{ addslashes($singular) }}&quot; content type. This action cannot be undone.',
                            confirmLabel: 'Delete',
                            formId: 'delete-type-{{ $type->id }}'
                        })"
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
                @endif
            </div>
        </div>

        {{-- Taxonomies --}}
        <div class="divide-y divide-gray-100 bg-gray-50/40">

            @forelse($type->taxonomies as $taxonomy)
            @php
                $txTrans    = $taxonomy->translations->first();
                $txSingular = $txTrans?->labels['singular'] ?? $taxonomy->name;
                $txPlural   = $txTrans?->labels['plural']   ?? $taxonomy->name;
                $txSlug     = $txTrans?->slug ?? $taxonomy->name;
            @endphp
            <div class="flex items-center gap-3 px-5 py-3 pl-14 hover:bg-gray-50 transition-colors">
                <div class="w-1.5 h-1.5 rounded-full bg-gray-300 shrink-0 -ml-5 mr-1.5"></div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700 font-medium">{{ $txPlural }}</span>
                        @if($taxonomy->is_system)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">Core</span>
                        @endif
                        @if($taxonomy->is_hierarchical)
                        <span class="px-1.5 py-0.5 bg-slate-100 text-slate-500 rounded text-xs">Hierarchical</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 font-mono">{{ $txSlug }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('cms.admin.terms.index', $taxonomy->id) }}"
                       class="text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors">Terms</a>
                    <span class="text-gray-200">|</span>
                    <a href="{{ route('cms.admin.taxonomies.edit', [$type->id, $taxonomy->id]) }}"
                       class="text-xs font-medium text-gray-500 hover:text-gray-800 transition-colors">Edit</a>

                    @if(! $taxonomy->is_system)
                    <span class="text-gray-200">|</span>
                    <form id="delete-tax-{{ $taxonomy->id }}"
                          method="POST"
                          action="{{ route('cms.admin.taxonomies.destroy', [$type->id, $taxonomy->id]) }}"
                          class="hidden">
                        @csrf @method('DELETE')
                    </form>
                    <button type="button"
                            @click="$dispatch('cms:confirm', {
                                title: 'Delete {{ addslashes($txPlural) }}?',
                                description: 'This will permanently delete the &quot;{{ addslashes($txSingular) }}&quot; taxonomy. All terms will be removed.',
                                confirmLabel: 'Delete',
                                formId: 'delete-tax-{{ $taxonomy->id }}'
                            })"
                            class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors">Delete</button>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-5 py-3 pl-14 text-xs text-gray-400 italic">No taxonomies yet.</div>
            @endforelse

            {{-- Add taxonomy link --}}
            <div class="px-5 py-2.5 pl-14">
                <a href="{{ route('cms.admin.taxonomies.create', $type->id) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Taxonomy
                </a>
            </div>
        </div>
    </div>
    @endforeach

    @if($types->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="text-base font-semibold text-gray-900 mb-1">No content types yet</h3>
        <p class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">Create your first content type to start managing custom post types.</p>
        <a href="{{ route('cms.admin.content-types.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Content Type
        </a>
    </div>
    @endif
</div>

@endsection
