{{--
 | Contensio - The open content platform for Laravel.
 | Admin — terms index.
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
    $txCreate = $txTrans?->labels['create'] ?? 'Add New';
@endphp

@section('title', $txPlural)

@section('breadcrumb')
    <a href="{{ route('contensio.account.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('contensio.account.content-types.index') }}" class="text-gray-500 hover:text-gray-700">Content Types</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">{{ $txPlural }}</span>
@endsection

@section('content')

@if (session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-3">
    {{ session('error') }}
</div>
@endif
@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-3">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ $txPlural }}</h1>
        @if($taxonomy->is_hierarchical)
        <p class="text-sm text-gray-400 mt-0.5">Hierarchical — terms can have parent/child relationships.</p>
        @else
        <p class="text-sm text-gray-400 mt-0.5">Flat — terms are independent (like tags).</p>
        @endif
    </div>
    <a href="{{ route('contensio.account.terms.create', $taxonomy->id) }}"
       class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-medium text-sm px-4 py-2 rounded-md transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ $txCreate }}
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-md overflow-hidden">

    @if($terms->isEmpty())
    <div class="px-6 py-16 text-center">
        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
        <h3 class="text-sm font-semibold text-gray-900 mb-1">No terms yet</h3>
        <p class="text-sm text-gray-400 mb-5">Add the first term to this taxonomy.</p>
        <a href="{{ route('contensio.account.terms.create', $taxonomy->id) }}"
           class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-medium text-sm px-4 py-2 rounded-md transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ $txCreate }}
        </a>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b-2 border-gray-100">
                <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-3">Name</th>
                <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-3 font-mono">Slug</th>
                @if($taxonomy->is_hierarchical)
                <th class="text-left text-xs font-bold text-gray-400 uppercase tracking-widest px-5 py-3">Parent</th>
                @endif
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @if($taxonomy->is_hierarchical)
                @foreach($terms as $term)
                    @php
                        $trans = $term->translations->firstWhere('language_id', $defaultLangId)
                            ?? $term->translations->first();
                    @endphp
                    @include('contensio::admin.terms.partials.row', [
                        'term'  => $term,
                        'trans' => $trans,
                        'depth' => 0,
                        'taxonomy' => $taxonomy,
                        'defaultLangId' => $defaultLangId,
                    ])
                    @foreach($term->children as $child)
                    @php
                        $childTrans = $child->translations->firstWhere('language_id', $defaultLangId)
                            ?? $child->translations->first();
                    @endphp
                    @include('contensio::admin.terms.partials.row', [
                        'term'  => $child,
                        'trans' => $childTrans,
                        'depth' => 1,
                        'taxonomy' => $taxonomy,
                        'defaultLangId' => $defaultLangId,
                    ])
                    @endforeach
                @endforeach
            @else
                @foreach($terms as $term)
                @php
                    $trans = $term->translations->firstWhere('language_id', $defaultLangId)
                        ?? $term->translations->first();
                @endphp
                @include('contensio::admin.terms.partials.row', [
                    'term'  => $term,
                    'trans' => $trans,
                    'depth' => 0,
                    'taxonomy' => $taxonomy,
                    'defaultLangId' => $defaultLangId,
                ])
                @endforeach
            @endif
        </tbody>
    </table>
    @endif
</div>

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@endsection
