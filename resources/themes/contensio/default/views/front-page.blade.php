{{--
 | front-page.blade.php — static front page.
 |
 | Rendered when "Homepage display" is set to "A static page" in Reading
 | settings. Receives the same variables as page.blade.php.
 |
 | Create this file in your theme to show a custom landing page instead of
 | the default blog-posts homepage. When this file exists it takes priority
 | over home.blade.php for the front page, regardless of the reading setting.
--}}
@extends('theme::layout')

@section('title', apply_filters('contensio/frontend/page-title', ($translation->meta_title ?: $translation->title) . ' — ' . $site['name'], $content))

@if($translation->meta_description)
@section('meta_description', $translation->meta_description)
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

    {{-- Title --}}
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight mb-8">
        {{ $translation->title }}
    </h1>

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
