@extends('theme::layout')

@section('title', ($termTrans->name ?? '') . ' — ' . ($taxTrans->labels['plural'] ?? ucfirst($taxonomy->name)) . ' — ' . $site['name'])

@section('content')

<div class="theme-container px-4 sm:px-6 py-12">

    {{-- Back --}}
    <a href="{{ route('contensio.blog') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 mb-8 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Blog
    </a>

    {{-- Heading --}}
    <div class="mb-10">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">
            {{ $taxTrans->labels['singular'] ?? ucfirst($taxonomy->name) }}
        </p>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
            {{ $termTrans->name }}
        </h1>
        @if($termTrans->description)
        <p class="mt-3 text-base text-gray-500">{{ $termTrans->description }}</p>
        @endif
    </div>

    @if($posts->isEmpty())
    <div class="text-center py-16">
        <p class="text-gray-400">No posts found.</p>
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
