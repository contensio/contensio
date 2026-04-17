{{--
 | Contensio - The open content platform for Laravel.
 | Admin — Reading settings.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('contensio::admin.layout')

@section('title', 'Reading settings')

@section('breadcrumb')
<a href="{{ route('contensio.account.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
<span class="mx-2 text-gray-400">/</span>
<span class="font-medium text-gray-700">Reading</span>
@endsection

@section('content')

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
    <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('contensio.account.settings.reading.save') }}">
@csrf

<div class="max-w-2xl space-y-6">

    {{-- Homepage Display --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5"
         x-data="{ display: '{{ $settings['homepage_display'] ?? 'latest_posts' }}' }">

        <h2 class="text-base font-bold text-gray-900">Homepage display</h2>

        <div class="space-y-3">
            <label class="flex items-start gap-3 cursor-pointer group">
                <input type="radio" name="homepage_display" value="latest_posts"
                       x-model="display"
                       class="mt-0.5 accent-ember-500">
                <div>
                    <span class="text-sm font-medium text-gray-800">Latest posts</span>
                    <p class="text-xs text-gray-500 mt-0.5">Your blog's most recent posts.</p>
                </div>
            </label>

            <label class="flex items-start gap-3 cursor-pointer group">
                <input type="radio" name="homepage_display" value="static_page"
                       x-model="display"
                       class="mt-0.5 accent-ember-500">
                <div>
                    <span class="text-sm font-medium text-gray-800">A static page</span>
                    <p class="text-xs text-gray-500 mt-0.5">Display a specific page as your homepage.</p>
                </div>
            </label>
        </div>

        <div x-show="display === 'static_page'" x-cloak class="pl-6 pt-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Homepage page</label>
            @if($pages->isEmpty())
            <p class="text-sm text-amber-600">No published pages found. Create and publish a page first.</p>
            @else
            <select name="homepage_page_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent bg-white">
                <option value="">— Select a page —</option>
                @foreach($pages as $page)
                @php $t = $page->translations->first(); @endphp
                <option value="{{ $page->id }}"
                    {{ ($settings['homepage_page_id'] ?? '') == $page->id ? 'selected' : '' }}>
                    {{ $t?->title ?? 'Page #' . $page->id }}
                </option>
                @endforeach
            </select>
            @endif
        </div>
    </div>

    {{-- Posts Per Page --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <h2 class="text-base font-bold text-gray-900">Blog & archive</h2>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Posts per page</label>
            <div class="flex items-center gap-3">
                <input type="number" name="posts_per_page"
                       value="{{ old('posts_per_page', $settings['posts_per_page'] ?? 12) }}"
                       min="1" max="100"
                       class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                <span class="text-sm text-gray-500">posts per page on archive/blog listings</span>
            </div>
        </div>
    </div>

    <div>
        <button type="submit"
                class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2 rounded-lg transition-colors">
            Save changes
        </button>
    </div>

</div>
</form>

@endsection
