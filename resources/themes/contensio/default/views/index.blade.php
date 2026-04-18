{{--
 | index.blade.php — ultimate fallback template.
 |
 | Rendered when no more specific template matches. In a complete theme this
 | should rarely (or never) be the final renderer for real content — it is a
 | safety net. Behaves like the home / posts-index page.
--}}
@extends('theme::layout')

@section('title', $site['name'])

@section('content')

<div class="theme-container py-16 text-center">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-4">{{ $site['name'] }}</h1>
    @if($site['tagline'])
    <p class="text-gray-500 text-lg mb-10">{{ $site['tagline'] }}</p>
    @endif

    @isset($posts)
    @if($posts->isNotEmpty())
    <div class="text-left divide-y divide-gray-100 mt-8">
        @foreach($posts as $post)
        @php
            $trans = method_exists($post, 'translations') ? $post->translations->first() : null;
            $slug  = $trans?->slug;
            $title = $trans?->title ?? 'Untitled';
        @endphp
        @if($slug)
        <div class="py-5">
            <h2 class="text-lg font-bold text-gray-900">
                <a href="{{ route('contensio.post', $slug) }}" class="hover:text-blue-600 transition-colors">
                    {{ $title }}
                </a>
            </h2>
        </div>
        @endif
        @endforeach
    </div>
    @else
    <p class="text-gray-400 text-sm mt-8">No content published yet.</p>
    @endif
    @endisset
</div>

@endsection
