{{--
 | Contensio - The open content platform for Laravel.
 | Frontend — archive.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::frontend.layout')

@section('title', 'Blog — ' . $site['name'])

@section('content')

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">

    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-10">Blog</h1>

    @if($posts->isEmpty())
    <div class="text-center py-16">
        <p class="text-gray-400">No posts published yet.</p>
    </div>
    @else

    <div class="divide-y divide-gray-100">
        @foreach($posts as $post)
        @php
            $trans = $post->translations->first();
            $slug  = $trans?->slug;
            $title = $trans?->title ?? 'Untitled';
        @endphp
        @if($slug)
        <article class="py-8 flex gap-6 group">

            {{-- Featured image --}}
            @if($post->featuredImage)
            <a href="{{ route('contensio.post', $slug) }}" class="shrink-0 hidden sm:block">
                <div class="w-28 h-20 rounded-lg overflow-hidden bg-gray-100">
                    <img src="{{ Storage::disk($post->featuredImage->disk)->url($post->featuredImage->file_path) }}"
                         alt="{{ $title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
            </a>
            @endif

            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-400 mb-1.5">
                    {{ $post->published_at?->format('M d, Y') }}
                    @if($post->author)
                    &middot; {{ $post->author->name }}
                    @endif
                </p>
                <h2 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors leading-snug">
                    <a href="{{ route('contensio.post', $slug) }}">{{ $title }}</a>
                </h2>
                @if($trans?->excerpt)
                <p class="mt-1.5 text-sm text-gray-500 line-clamp-2">{{ $trans->excerpt }}</p>
                @endif
                <a href="{{ route('contensio.post', $slug) }}"
                   class="inline-flex items-center gap-1 mt-3 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                    Read more
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </article>
        @endif
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($posts->hasPages())
    <div class="mt-8 flex justify-center">
        {{ $posts->links() }}
    </div>
    @endif

    @endif

</div>

@endsection
