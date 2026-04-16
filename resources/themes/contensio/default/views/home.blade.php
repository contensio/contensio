@extends('theme::layout')

@section('content')

{{-- Hero --}}
<section class="max-w-4xl mx-auto px-4 sm:px-6 pt-16 pb-12 text-center">
    <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
        {{ $site['name'] }}
    </h1>
    @if($site['tagline'])
    <p class="mt-4 text-lg text-gray-500 max-w-xl mx-auto">{{ $site['tagline'] }}</p>
    @endif
</section>

{{-- Recent posts --}}
@if($posts->isNotEmpty())
<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-16">
    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">Latest Posts</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($posts as $post)
        @php
            $trans = $post->translations->first();
            $slug  = $trans?->slug;
            $title = $trans?->title ?? 'Untitled';
        @endphp
        @if($slug)
        <article class="group">
            @if($post->featuredImage)
            <a href="{{ route('contensio.post', $slug) }}">
                <div class="aspect-video rounded-xl overflow-hidden mb-3 bg-gray-100">
                    <img src="{{ Storage::disk($post->featuredImage->disk)->url($post->featuredImage->file_path) }}"
                         alt="{{ $title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
            </a>
            @endif
            <p class="text-xs text-gray-400 mb-1.5">{{ $post->published_at?->format('M d, Y') }}</p>
            <h3 class="font-bold text-gray-900 group-hover:text-blue-600 transition-colors leading-snug">
                <a href="{{ route('contensio.post', $slug) }}">{{ $title }}</a>
            </h3>
            @if($trans?->excerpt)
            <p class="mt-1.5 text-sm text-gray-500 line-clamp-2">{{ $trans->excerpt }}</p>
            @endif
        </article>
        @endif
        @endforeach
    </div>

    <div class="mt-10 text-center">
        <a href="{{ route('contensio.blog') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
            View all posts
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</section>
@else
<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-16 text-center">
    <p class="text-gray-400 text-sm">No posts yet.</p>
</section>
@endif

@endsection
