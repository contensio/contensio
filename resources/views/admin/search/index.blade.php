{{--
 | Contensio - The open content platform for Laravel.
 | Admin — global search results.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Search')

@section('breadcrumb')
    <span class="text-gray-900 font-medium">Search</span>
@endsection

@section('content')

<div class="mb-6">
    <form method="GET" action="{{ route('contensio.account.search') }}" class="flex items-center gap-3">
        <div class="relative flex-1 max-w-xl">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="q" value="{{ $q }}" autofocus
                   placeholder="Search content, media…"
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
        </div>
        <button type="submit"
                class="bg-ember-500 hover:bg-ember-600 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
            Search
        </button>
    </form>
</div>

@if ($results === null)

<div class="bg-white border border-gray-200 rounded-md p-16 text-center">
    <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    <p class="text-sm text-gray-400">Enter at least 2 characters to search.</p>
</div>

@elseif ($results['content']->isEmpty() && $results['media']->isEmpty())

<div class="bg-white border border-gray-200 rounded-md p-16 text-center">
    <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    <p class="text-sm font-medium text-gray-500 mb-1">No results for "{{ $q }}"</p>
    <p class="text-xs text-gray-400">Try different keywords.</p>
</div>

@else

{{-- Content results --}}
@if ($results['content']->isNotEmpty())
<div class="mb-6">
    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">
        Content
        <span class="ml-1 font-normal normal-case text-gray-300">({{ $results['content']->count() }})</span>
    </h2>
    <div class="bg-white border border-gray-200 rounded-md overflow-hidden divide-y divide-gray-100">
        @foreach($results['content'] as $trans)
        @php
            $item     = $trans->content;
            $typeName = $item->contentType->name ?? 'page';
            $editRoute = match($typeName) {
                'page' => route('contensio.account.pages.edit', $item->id),
                'post' => route('contensio.account.posts.edit', $item->id),
                default => route('contensio.account.content.edit', [$typeName, $item->id]),
            };
            $ctTrans  = $item->contentType?->translations->first();
            $typeLabel = $ctTrans?->labels['singular'] ?? ucfirst($typeName);
        @endphp
        <a href="{{ $editRoute }}"
           class="flex items-center gap-4 px-4 py-3.5 hover:bg-blue-50/40 transition-colors group">
            <div class="w-8 h-8 rounded-md bg-gray-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors truncate">
                    {{ $trans->title ?: '(untitled)' }}
                </p>
                @if($trans->excerpt)
                <p class="text-xs text-gray-400 truncate mt-0.5">{{ $trans->excerpt }}</p>
                @endif
            </div>
            <div class="shrink-0 flex items-center gap-3">
                <span class="text-xs text-gray-400">{{ $typeLabel }}</span>
                @if($item->status === 'published')
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-50 text-green-700 border border-green-200">Published</span>
                @elseif($item->status === 'scheduled')
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Scheduled</span>
                @else
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Draft</span>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- Media results --}}
@if ($results['media']->isNotEmpty())
<div>
    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">
        Media
        <span class="ml-1 font-normal normal-case text-gray-300">({{ $results['media']->count() }})</span>
    </h2>
    <div class="bg-white border border-gray-200 rounded-md overflow-hidden divide-y divide-gray-100">
        @foreach($results['media'] as $media)
        <a href="{{ route('contensio.account.media.index') }}"
           class="flex items-center gap-4 px-4 py-3 hover:bg-blue-50/40 transition-colors group">
            <div class="w-10 h-10 rounded-md overflow-hidden bg-gray-100 shrink-0">
                @if(str_starts_with($media->mime_type ?? '', 'image/'))
                <img src="{{ $media->variantUrl('thumbnail') }}" alt="" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors truncate">
                    {{ $media->file_name }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $media->mime_type }} · {{ $media->folder }}</p>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

@endif

@endsection
