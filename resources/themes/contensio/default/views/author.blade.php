@extends('theme::layout')

@section('title', $user->name . ' · ' . $site['name'])
@section('meta_description', $user->bio ? \Illuminate\Support\Str::limit(strip_tags($user->bio), 160) : 'Author profile for ' . $user->name . ' on ' . $site['name'] . '.')

@section('content')

<div class="theme-container py-14">

    {{-- Author card --}}
    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-7 mb-6">

        {{-- Avatar --}}
        <div class="shrink-0">
            @if($user->avatar_path)
            <img src="{{ Storage::disk('public')->url($user->avatar_path) }}"
                 alt="{{ $user->name }}"
                 class="w-24 h-24 rounded-full object-cover ring-2 ring-gray-100">
            @else
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-slate-400 to-slate-600
                        flex items-center justify-center text-white text-3xl font-bold ring-2 ring-gray-100">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            @endif
        </div>

        {{-- Name + member since --}}
        <div class="flex-1 text-center sm:text-left">
            <h1 class="text-3xl font-bold text-gray-900 mb-1">{{ $user->name }}</h1>
            <p class="text-gray-400">Member since {{ $user->created_at->format('F Y') }}</p>
        </div>

    </div>

    {{-- Bio — full container width --}}
    @if($user->bio)
    <p class="text-gray-600 leading-relaxed mb-12">{{ $user->bio }}</p>
    @endif

    <hr class="border-gray-100 mb-10">

    {{-- Posts by this author --}}
    @if($posts->isNotEmpty())
    <div>
        <h2 class="text-xl font-bold text-gray-900 mb-6">
            Posts by {{ $user->name }}
        </h2>

        <div class="divide-y divide-gray-100">
            @foreach($posts as $post)
            @php
                $trans   = $post->translations->first();
                $title   = $trans?->title ?? 'Untitled';
                $excerpt = $trans?->excerpt ?? null;
                $slug    = $trans?->slug ?? null;
            @endphp
            <article class="py-6 flex gap-5 group">

                @if($post->featuredImage)
                <a href="{{ $slug ? route('contensio.post', $slug) : '#' }}" class="shrink-0 hidden sm:block">
                    <div class="w-28 h-20 rounded-lg overflow-hidden bg-gray-100">
                        <img src="{{ Storage::disk($post->featuredImage->disk)->url($post->featuredImage->file_path) }}"
                             alt="{{ $title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                </a>
                @endif

                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 leading-snug mb-2">
                        @if($slug)
                        <a href="{{ route('contensio.post', $slug) }}" class="hover:text-blue-600 transition-colors">{{ $title }}</a>
                        @else
                        {{ $title }}
                        @endif
                    </h3>
                    @if($excerpt)
                    <p class="text-gray-500 line-clamp-2 mb-2">{{ $excerpt }}</p>
                    @endif
                    @if($post->published_at)
                    <time class="text-gray-400">{{ $post->published_at->format('M j, Y') }}</time>
                    @endif
                </div>

            </article>
            @endforeach
        </div>
    </div>
    @else
    <p class="py-12 text-center text-gray-400">No published posts yet.</p>
    @endif

</div>

@endsection
