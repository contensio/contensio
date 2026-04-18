{{--
 | Contensio - The open content platform for Laravel.
 | Frontend — page.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::frontend.layout')

@section('title', apply_filters('contensio/frontend/page-title', ($translation->meta_title ?: $translation->title) . ' — ' . $site['name'], $content))

@if($translation->meta_description)
@section('meta_description', $translation->meta_description)
@endif

@section('content')

<article class="max-w-3xl mx-auto px-4 sm:px-6 py-12">

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
            @include('contensio::frontend.partials.block', ['block' => $block, 'langId' => $lang?->id])
        @endforeach
    </div>
    @php $__body = ob_get_clean(); @endphp
    {!! apply_filters('contensio/frontend/content-body', $__body, $content) !!}

</article>

@endsection
