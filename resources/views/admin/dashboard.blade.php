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

@section('title', 'Dashboard')

@section('breadcrumb')
<span class="font-medium text-gray-700">Dashboard</span>
@endsection

@section('content')

@php
    // Resolve edit route for a content item (handles page/post/custom types)
    $editRouteFor = function ($item) {
        $type = $item->contentType?->name;
        return match ($type) {
            'page'  => route('cms.admin.pages.edit',   $item->id),
            'post'  => route('cms.admin.posts.edit',   $item->id),
            default => route('cms.admin.content.edit', [$type ?: 'page', $item->id]),
        };
    };

    $hour = (int) now()->format('G');
    $greeting = match (true) {
        $hour < 12 => 'Good morning',
        $hour < 18 => 'Good afternoon',
        default    => 'Good evening',
    };
@endphp

{{-- Header + quick actions --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ $greeting }}, {{ auth()->user()->name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">Here's what's happening on your site.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('cms.admin.pages.create') }}"
           class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-3 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New page
        </a>
        <a href="{{ route('cms.admin.posts.create') }}"
           class="inline-flex items-center gap-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-3 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New post
        </a>
        <a href="{{ route('cms.admin.media.index') }}"
           class="inline-flex items-center gap-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-3 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Upload media
        </a>
    </div>
</div>

{{-- Stats — 5 cards, each linking to the relevant section --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">

    <a href="{{ route('cms.admin.pages.index') }}"
       class="group bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-sm p-4 transition-all">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pages</span>
            <div class="w-8 h-8 bg-blue-50 group-hover:bg-blue-100 rounded-lg flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pages']) }}</p>
    </a>

    <a href="{{ route('cms.admin.posts.index') }}"
       class="group bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-sm p-4 transition-all">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Posts</span>
            <div class="w-8 h-8 bg-indigo-50 group-hover:bg-indigo-100 rounded-lg flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['posts']) }}</p>
    </a>

    <a href="{{ route('cms.admin.media.index') }}"
       class="group bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-sm p-4 transition-all">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Media</span>
            <div class="w-8 h-8 bg-purple-50 group-hover:bg-purple-100 rounded-lg flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['media']) }}</p>
    </a>

    <a href="{{ route('cms.admin.users.index') }}"
       class="group bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-sm p-4 transition-all">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Users</span>
            <div class="w-8 h-8 bg-green-50 group-hover:bg-green-100 rounded-lg flex items-center justify-center transition-colors">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['users']) }}</p>
    </a>

    <div class="bg-white rounded-xl border {{ $stats['drafts'] > 0 ? 'border-amber-200 bg-amber-50/30' : 'border-gray-200' }} p-4">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium uppercase tracking-wide {{ $stats['drafts'] > 0 ? 'text-amber-700' : 'text-gray-500' }}">Drafts</span>
            <div class="w-8 h-8 {{ $stats['drafts'] > 0 ? 'bg-amber-100' : 'bg-gray-50' }} rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 {{ $stats['drafts'] > 0 ? 'text-amber-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
        </div>
        <p class="text-2xl font-bold {{ $stats['drafts'] > 0 ? 'text-amber-900' : 'text-gray-900' }}">{{ number_format($stats['drafts']) }}</p>
    </div>

</div>

{{-- Main widget grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Recent Published — wider column --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
                <h2 class="text-base font-bold text-gray-900">Recently published</h2>
                <p class="text-xs text-gray-500 mt-0.5">Your latest live content.</p>
            </div>
        </div>

        @if($recentPublished->isEmpty())
        <div class="px-5 py-14 text-center">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium text-gray-700 mb-1">Nothing published yet</p>
            <p class="text-xs text-gray-400 mb-4">Publish your first page or post to see it here.</p>
            <a href="{{ route('cms.admin.posts.create') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Create a post →</a>
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($recentPublished as $item)
            @php
                $tr    = $item->translations->first();
                $title = $tr?->title ?: 'Untitled';
                $type  = $item->contentType?->name;
            @endphp
            <a href="{{ $editRouteFor($item) }}"
               class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition-colors group">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-600 uppercase tracking-wider shrink-0">
                    {{ $type ?: '—' }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors">{{ $title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        @if($item->author){{ $item->author->name }} · @endif
                        {{ $item->published_at?->diffForHumans() ?? $item->updated_at->diffForHumans() }}
                    </p>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Recent Drafts — narrower column --}}
    <div class="bg-white rounded-xl border {{ $recentDrafts->isNotEmpty() ? 'border-amber-200' : 'border-gray-200' }}">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
                <h2 class="text-base font-bold text-gray-900">Drafts in progress</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pick up where you left off.</p>
            </div>
        </div>

        @if($recentDrafts->isEmpty())
        <div class="px-5 py-14 text-center">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-medium text-gray-700 mb-1">No drafts right now</p>
            <p class="text-xs text-gray-400">Clean plate. ✨</p>
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($recentDrafts as $item)
            @php
                $tr    = $item->translations->first();
                $title = $tr?->title ?: 'Untitled';
            @endphp
            <a href="{{ $editRouteFor($item) }}"
               class="block px-5 py-3 hover:bg-amber-50/40 transition-colors group">
                <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-amber-700 transition-colors">{{ $title }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ ucfirst($item->contentType?->name ?? 'content') }} · Updated {{ $item->updated_at->diffForHumans() }}
                </p>
            </a>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- Activity log — full width --}}
<div class="bg-white rounded-xl border border-gray-200">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="text-base font-bold text-gray-900">Activity</h2>
        <p class="text-xs text-gray-500 mt-0.5">Recent changes across the site.</p>
    </div>

    @if($recentActivity->isEmpty())
    <div class="px-5 py-12 text-center">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-medium text-gray-700 mb-1">No activity yet</p>
        <p class="text-xs text-gray-400">Actions will show up here as you work.</p>
    </div>
    @else
    <div class="divide-y divide-gray-100">
        @foreach($recentActivity as $log)
        <div class="flex items-start gap-3 px-5 py-3">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-slate-500 to-slate-700 flex items-center justify-center shrink-0 text-xs font-bold text-white">
                {{ strtoupper(substr($log->user?->name ?? '?', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-800">
                    <span class="font-semibold">{{ $log->user?->name ?? 'System' }}</span>
                    <span class="text-gray-600">{{ $log->action }}</span>
                    @if($log->subject_type)
                    <span class="text-gray-400 text-xs">{{ class_basename($log->subject_type) }}</span>
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection
