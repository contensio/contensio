@extends('theme::layout')

@section('title', apply_filters('contensio/frontend/page-title', ($translation->meta_title ?: $translation->title) . ' — ' . $site['name'], $content))

@if($translation->meta_description)
@section('meta_description', $translation->meta_description)
@elseif(isset($translation->excerpt) && $translation->excerpt)
@section('meta_description', $translation->excerpt)
@endif

@section('content')

<article class="theme-container py-12">

    {{-- Featured image --}}
    @if($content->featuredImage)
    <div class="aspect-video rounded-2xl overflow-hidden mb-8 bg-gray-100">
        <img src="{{ Storage::disk($content->featuredImage->disk)->url($content->featuredImage->file_path) }}"
             alt="{{ $translation->title }}"
             class="w-full h-full object-cover">
    </div>
    @endif

    {{-- Meta --}}
    @if($content->author || $content->published_at)
    <div class="flex items-center gap-3 text-sm text-gray-400 mb-6">
        @if($content->author)
        <span class="font-medium text-gray-600">{{ $content->author->name }}</span>
        @endif
        @if($content->author && $content->published_at)<span>&middot;</span>@endif
        @if($content->published_at)
        <time datetime="{{ $content->published_at->toDateString() }}">
            {{ $content->published_at->format('M d, Y') }}
        </time>
        @endif
    </div>
    @endif

    {{-- Title --}}
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-6">
        {{ $translation->title }}
    </h1>

    {{-- Excerpt --}}
    @if(isset($translation->excerpt) && $translation->excerpt)
    <p class="text-lg text-gray-500 leading-relaxed mb-8 border-l-4 border-gray-200 pl-4">
        {{ $translation->excerpt }}
    </p>
    @endif

    {{-- Blocks --}}
    @php ob_start(); @endphp
    <div class="space-y-6">
        @foreach($content->blocks ?? [] as $block)
            @include('theme::partials.block', ['block' => $block, 'langId' => $lang?->id])
        @endforeach
    </div>
    @php $__body = ob_get_clean(); @endphp
    {!! apply_filters('contensio/frontend/content-body', $__body, $content) !!}

</article>

@endsection
