{{--
 | Contensio - The open content platform for Laravel.
 | Admin — dashboard.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', __('cms::admin.dashboard.title'))

@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('cms::admin.dashboard.title') }}</span>
@endsection

@section('content')

{{-- Page header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900">{{ __('cms::admin.dashboard.title') }}</h1>
    <p class="text-sm text-gray-500 mt-0.5">{{ __('cms::admin.dashboard.welcome', ['name' => auth()->user()->name]) }}</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('cms::admin.dashboard.stats.content') }}</span>
            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['contents']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('cms::admin.dashboard.stats.content_subtitle') }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('cms::admin.dashboard.stats.media') }}</span>
            <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['media']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('cms::admin.dashboard.stats.media_subtitle') }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('cms::admin.dashboard.stats.comments') }}</span>
            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending_comments']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('cms::admin.dashboard.stats.comments_subtitle') }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('cms::admin.dashboard.stats.users') }}</span>
            <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['users']) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('cms::admin.dashboard.stats.users_subtitle') }}</p>
    </div>

</div>

{{-- Content + Activity --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Content --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">{{ __('cms::admin.dashboard.recent_content') }}</h2>
            <a href="#" class="text-xs text-blue-600 hover:text-blue-700 font-medium">{{ __('cms::admin.dashboard.view_all') }}</a>
        </div>

        @if($recentContent->isEmpty())
        <div class="px-5 py-10 text-center">
            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-gray-400">{{ __('cms::admin.dashboard.no_content') }}</p>
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($recentContent as $item)
            <div class="flex items-center gap-3 px-5 py-3.5">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ $item->translations->first()?->title ?? __('cms::admin.dashboard.untitled') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $item->contentType->name ?? '' }}
                        @if($item->author) · {{ $item->author->name }} @endif
                        · {{ $item->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $item->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $item->status }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">{{ __('cms::admin.dashboard.recent_activity') }}</h2>
        </div>

        @if($recentActivity->isEmpty())
        <div class="px-5 py-10 text-center">
            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-gray-400">{{ __('cms::admin.dashboard.no_activity') }}</p>
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($recentActivity as $log)
            <div class="flex items-start gap-3 px-5 py-3.5">
                <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center shrink-0 mt-0.5 text-xs font-semibold text-gray-500">
                    {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-700">
                        <span class="font-medium">{{ $log->user?->name ?? 'System' }}</span>
                        {{ $log->action }}
                        <span class="text-gray-400">{{ $log->subject_type }}</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

@endsection
