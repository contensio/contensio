{{--
 | Contensio - The open content platform for Laravel.
 | Admin - Browse plugins from the Contensio directory.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Browse Plugins')

@section('breadcrumb')
<a href="{{ route('contensio.account.plugins.index') }}" class="text-gray-500 hover:text-gray-700">Plugins</a>
<span class="text-gray-400 mx-1">/</span>
<span class="font-medium text-gray-700">Browse</span>
@endsection

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Browse Plugins</h1>
            <p class="text-sm text-gray-500 mt-0.5">Discover and install plugins from the Contensio directory.</p>
        </div>
        <a href="{{ route('contensio.account.plugins.index') }}"
           class="inline-flex items-center gap-2 text-sm text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="bi bi-arrow-left"></i> Installed Plugins
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>
    @endif

    @if($error)
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ $error }}</div>
    @endif

    {{-- Search + sort --}}
    <form method="GET" class="flex gap-3 mb-6">
        <div class="flex-1">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search plugins..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-ember-400 focus:border-transparent">
        </div>
        <select name="sort"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-ember-400 focus:border-transparent">
            <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Most Popular</option>
            <option value="newest"  {{ $sort === 'newest'  ? 'selected' : '' }}>Newest</option>
            <option value="updated" {{ $sort === 'updated' ? 'selected' : '' }}>Recently Updated</option>
            <option value="name"    {{ $sort === 'name'    ? 'selected' : '' }}>Name A-Z</option>
        </select>
        <button type="submit"
                class="px-4 py-2 bg-ember-500 hover:bg-ember-600 text-white text-sm font-medium rounded-lg transition-colors">
            Search
        </button>
        @if($search)
            <a href="{{ route('contensio.account.plugins.browse') }}"
               class="px-3 py-2 border border-gray-300 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition-colors">Clear</a>
        @endif
    </form>

    {{-- Plugin grid --}}
    @if(empty($plugins))
        <div class="bg-white border border-gray-200 rounded-xl py-16 text-center text-gray-400">
            <i class="bi bi-puzzle text-4xl block mb-3"></i>
            <p class="text-lg font-medium text-gray-500">No plugins found</p>
            @if($search)
                <p class="text-sm mt-1">Try a different search term.</p>
            @else
                <p class="text-sm mt-1">The plugin directory is empty or could not be reached.</p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($plugins as $plugin)
            @php
                $isInstalled = in_array($plugin['slug'], $installed);
            @endphp
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden flex flex-col hover:border-gray-300 transition-colors">

                {{-- Banner --}}
                @if($plugin['banner_url'] ?? null)
                    <div class="h-32 bg-gray-100 overflow-hidden">
                        <img src="{{ $plugin['banner_url'] }}" alt="" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="h-20 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center">
                        <i class="bi bi-puzzle text-3xl text-gray-200"></i>
                    </div>
                @endif

                <div class="p-4 flex flex-col flex-1">

                    {{-- Title + version --}}
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <h3 class="font-semibold text-gray-900 text-sm leading-tight">{{ $plugin['name'] }}</h3>
                        <span class="text-xs text-gray-400 whitespace-nowrap font-mono">v{{ $plugin['version'] }}</span>
                    </div>

                    {{-- Description --}}
                    <p class="text-xs text-gray-500 mb-3 flex-1 leading-relaxed">{{ $plugin['short_description'] }}</p>

                    {{-- Meta --}}
                    <div class="flex items-center gap-3 text-xs text-gray-400 mb-3">
                        <span><i class="bi bi-person"></i> {{ $plugin['author'] }}</span>
                        <span><i class="bi bi-download"></i> {{ number_format($plugin['download_count']) }}</span>
                        @if($plugin['license'] ?? null)
                            <span class="uppercase">{{ $plugin['license'] }}</span>
                        @endif
                    </div>

                    {{-- Tags --}}
                    @if($plugin['tags'] ?? [])
                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach(array_slice($plugin['tags'], 0, 4) as $tag)
                                <span class="text-xs bg-gray-100 text-gray-500 rounded px-1.5 py-0.5">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Action --}}
                    @if($isInstalled)
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-600">
                                <i class="bi bi-check-circle-fill"></i> Installed
                            </span>
                        </div>
                    @else
                        <form action="{{ route('contensio.account.plugins.installFromDirectory') }}" method="POST">
                            @csrf
                            <input type="hidden" name="slug" value="{{ $plugin['slug'] }}">
                            <button type="submit"
                                    class="w-full px-3 py-1.5 bg-ember-500 hover:bg-ember-600 text-white text-xs font-semibold rounded-lg transition-colors"
                                    onclick="this.disabled=true;this.textContent='Installing...';this.form.submit();">
                                Install
                            </button>
                        </form>
                    @endif

                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($meta['last_page'] > 1)
            <div class="flex items-center justify-center gap-2 mt-6">
                @if($meta['current_page'] > 1)
                    <a href="{{ route('contensio.account.plugins.browse', ['search' => $search, 'sort' => $sort, 'page' => $meta['current_page'] - 1]) }}"
                       class="px-3 py-1.5 border border-gray-300 text-gray-600 text-sm rounded-lg hover:bg-gray-50">&larr; Previous</a>
                @endif
                <span class="text-sm text-gray-500">Page {{ $meta['current_page'] }} of {{ $meta['last_page'] }}</span>
                @if($meta['current_page'] < $meta['last_page'])
                    <a href="{{ route('contensio.account.plugins.browse', ['search' => $search, 'sort' => $sort, 'page' => $meta['current_page'] + 1]) }}"
                       class="px-3 py-1.5 border border-gray-300 text-gray-600 text-sm rounded-lg hover:bg-gray-50">Next &rarr;</a>
                @endif
            </div>
        @endif
    @endif

</div>

@endsection
