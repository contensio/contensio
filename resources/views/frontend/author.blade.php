{{--
 | Contensio - The open content platform for Laravel.
 | Frontend — public author profile page.
 | https://contensio.com
 |
 | Variables:
 |   $user  — User model (is_active=true)
 |   $posts — Collection of recent published posts by this author
 |   $site  — ['name' => ..., 'tagline' => ...]
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::frontend.layout')

@section('title', $user->name . ' · ' . $site['name'])
@section('meta_description', $user->bio ? Str::limit(strip_tags($user->bio), 160) : 'Author profile for ' . $user->name . ' on ' . $site['name'] . '.')

@section('content')

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-14">

    {{-- Author card --}}
    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-7 mb-12">

        {{-- Avatar --}}
        <div class="shrink-0">
            @if($user->avatar_path)
            <img src="{{ asset('storage/' . $user->avatar_path) }}"
                 alt="{{ $user->name }}"
                 class="w-24 h-24 rounded-full object-cover ring-2 ring-gray-100">
            @else
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-slate-400 to-slate-600
                        flex items-center justify-center text-white text-3xl font-bold ring-2 ring-gray-100">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="flex-1 text-center sm:text-left">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $user->name }}</h1>

            <p class="text-sm text-gray-400 mb-4">
                Member since {{ $user->created_at->format('F Y') }}
            </p>

            @if($user->bio)
            <p class="text-base text-gray-600 leading-relaxed max-w-prose">{{ $user->bio }}</p>
            @endif
        </div>

    </div>

    {{-- Recent posts --}}
    @if($posts->isNotEmpty())
    <div>
        <h2 class="text-lg font-bold text-gray-900 mb-6 pb-3 border-b border-gray-100">
            Posts by {{ $user->name }}
        </h2>

        <div class="space-y-8">
            @foreach($posts as $post)
            @php
                $translation  = $post->translations->first();
                $postTitle    = $translation?->title ?? 'Untitled';
                $postExcerpt  = $translation?->excerpt ?? null;
                $postSlug     = $translation?->slug ?? null;
                $featuredImg  = $post->featuredImage;
            @endphp
            <article class="flex gap-5 group">

                {{-- Thumbnail --}}
                @if($featuredImg && $featuredImg->path)
                <a href="{{ $postSlug ? route('contensio.post', $postSlug) : '#' }}"
                   class="shrink-0 w-24 h-20 sm:w-32 sm:h-24 rounded-lg overflow-hidden bg-gray-100">
                    <img src="{{ asset('storage/' . $featuredImg->path) }}"
                         alt="{{ $postTitle }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                </a>
                @endif

                {{-- Text --}}
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 mb-1 leading-snug">
                        @if($postSlug)
                        <a href="{{ route('contensio.post', $postSlug) }}"
                           class="hover:text-blue-600 transition-colors">
                            {{ $postTitle }}
                        </a>
                        @else
                        {{ $postTitle }}
                        @endif
                    </h3>

                    @if($postExcerpt)
                    <p class="text-sm text-gray-500 line-clamp-2 leading-relaxed mb-2">{{ $postExcerpt }}</p>
                    @endif

                    <time class="text-xs text-gray-400">
                        {{ $post->published_at?->format('M j, Y') }}
                    </time>
                </div>

            </article>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-12 text-gray-400 text-sm">
        No published posts yet.
    </div>
    @endif

</div>

@endsection
