@extends('theme::layout')

@section('title', ($translation->meta_title ?: $translation->title) . ' — ' . $site['name'])

@if($translation->meta_description)
@section('meta_description', $translation->meta_description)
@endif

@section('content')

<article class="max-w-3xl mx-auto px-4 sm:px-6 py-12">

    {{-- Back to blog --}}
    <a href="{{ route('contensio.blog') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-700 mb-8 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Blog
    </a>

    {{-- Featured image --}}
    @if($content->featuredImage)
    <div class="aspect-video rounded-2xl overflow-hidden mb-8 bg-gray-100">
        <img src="{{ Storage::disk($content->featuredImage->disk)->url($content->featuredImage->file_path) }}"
             alt="{{ $translation->title }}"
             class="w-full h-full object-cover">
    </div>
    @endif

    {{-- Meta --}}
    <div class="flex items-center gap-3 text-sm text-gray-400 mb-6">
        @if($content->author)
        <span class="font-medium text-gray-600">{{ $content->author->name }}</span>
        <span>&middot;</span>
        @endif
        <time datetime="{{ $content->published_at?->toDateString() }}">
            {{ $content->published_at?->format('M d, Y') }}
        </time>
    </div>

    {{-- Title --}}
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-6">
        {{ $translation->title }}
    </h1>

    {{-- Excerpt --}}
    @if($translation->excerpt)
    <p class="text-lg text-gray-500 leading-relaxed mb-8 border-l-4 border-gray-200 pl-4">
        {{ $translation->excerpt }}
    </p>
    @endif

    {{-- Blocks --}}
    <div class="space-y-6">
        @foreach($content->blocks ?? [] as $block)
            @include('theme::partials.block', ['block' => $block, 'langId' => $lang?->id])
        @endforeach
    </div>

</article>

@include('contensio::frontend.partials.comments', [
    'content'         => $content,
    'comments'        => $comments,
    'commentsEnabled' => $commentsEnabled,
])

@endsection
