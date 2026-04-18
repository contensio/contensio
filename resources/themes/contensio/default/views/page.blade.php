@extends('theme::layout')

@section('title', ($translation->meta_title ?: $translation->title) . ' — ' . $site['name'])
@section('meta_title', $translation->meta_title ?: $translation->title)

@if($translation->meta_description)
@section('meta_description', $translation->meta_description)
@endif

@if($content->featuredImage)
@section('og_image', Storage::disk($content->featuredImage->disk)->url($content->featuredImage->file_path))
@endif

@section('content')

<article class="theme-container px-4 sm:px-6 py-12">

    {{-- Featured image --}}
    @if($content->featuredImage)
    <div class="aspect-video rounded-2xl overflow-hidden mb-8 bg-gray-100">
        <img src="{{ Storage::disk($content->featuredImage->disk)->url($content->featuredImage->file_path) }}"
             alt="{{ $translation->title }}"
             class="w-full h-full object-cover">
    </div>
    @endif

    {{-- Title --}}
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight mb-8">
        {{ $translation->title }}
    </h1>

    {{-- Blocks --}}
    <div class="space-y-6">
        @foreach($content->blocks ?? [] as $block)
            @include('theme::partials.block', ['block' => $block, 'langId' => $lang?->id])
        @endforeach
    </div>

</article>

@endsection
