@extends('theme::layout')

@section('title', ($query ? "Search: {$query}" : 'Search') . ' — ' . $site['name'])

@section('content')

<div class="theme-container px-4 sm:px-6 py-12">

    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-8">Search</h1>

    {{-- Search box --}}
    <form method="GET" action="{{ route('contensio.search') }}" class="mb-10">
        <div class="flex items-center gap-2">
            <input type="search"
                   name="q"
                   value="{{ $query }}"
                   placeholder="Search posts and pages…"
                   autofocus
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 text-base
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <button type="submit"
                    class="bg-gray-900 hover:bg-gray-700 text-white font-semibold px-5 py-2.5 rounded-lg transition-colors text-sm">
                Search
            </button>
        </div>
    </form>

    @if($searched)

        @if($results->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-400">No results found for <strong class="text-gray-700">{{ $query }}</strong>.</p>
        </div>

        @else

        <p class="text-sm text-gray-500 mb-6">
            {{ $results->total() }} {{ Str::plural('result', $results->total()) }} for
            <strong class="text-gray-800">{{ $query }}</strong>
        </p>

        <div class="space-y-8">
            @foreach($results as $trans)
            @php
                $content  = $trans->content;
                $typeName = $content->contentType?->name ?? 'page';
                $route    = $typeName === 'post'
                    ? route('contensio.post', $trans->slug)
                    : route('contensio.page', $trans->slug);
            @endphp
            <article class="group border-b border-gray-100 pb-8 last:border-0">
                @if($content->featuredImage)
                <div class="mb-3 w-full aspect-video rounded-xl overflow-hidden bg-gray-100">
                    <img src="{{ Storage::disk($content->featuredImage->disk)->url($content->featuredImage->file_path) }}"
                         alt="{{ $trans->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                @endif

                <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                    <span class="inline-block bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-medium capitalize">
                        {{ $typeName }}
                    </span>
                    @if($content->published_at)
                    <span>{{ $content->published_at->format('M d, Y') }}</span>
                    @endif
                    @if($content->author)
                    <span>&middot; {{ $content->author->name }}</span>
                    @endif
                </div>

                <h2 class="text-xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors mb-2">
                    <a href="{{ $route }}">{{ $trans->title }}</a>
                </h2>

                @if($trans->excerpt)
                <p class="text-sm text-gray-500 line-clamp-2">{{ $trans->excerpt }}</p>
                @endif

                <a href="{{ $route }}"
                   class="inline-flex items-center gap-1 mt-3 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                    Read more
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($results->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $results->links() }}
        </div>
        @endif

        @endif

    @endif

</div>

@endsection
