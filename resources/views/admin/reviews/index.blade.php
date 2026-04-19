{{--
 | Contensio - The open content platform for Laravel.
 | Admin — content review queue.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('contensio::admin.layout')

@section('title', 'Review Queue')

@section('breadcrumb')
    <span class="text-gray-900 font-medium">Review Queue</span>
@endsection

@section('content')

@if (session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-3">
    {{ session('error') }}
</div>
@endif
@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-3">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Review Queue</h1>
        <p class="text-sm text-gray-400 mt-0.5">Content submitted by authors awaiting your decision.</p>
    </div>
    @if($items->isNotEmpty())
    <span class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 rounded-full text-sm font-semibold bg-amber-100 text-amber-700">
        {{ $items->count() }}
    </span>
    @endif
</div>

@if($items->isEmpty())

<div class="bg-white border border-gray-200 rounded-md p-16 text-center">
    <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h3 class="text-sm font-semibold text-gray-900 mb-1">All caught up!</h3>
    <p class="text-sm text-gray-400 max-w-xs mx-auto">No content is currently awaiting review.</p>
</div>

@else

<div class="space-y-3">
    @foreach($items as $item)
    @php
        $title    = $item->translations->first()?->title ?? 'Untitled';
        $type     = $item->contentType?->name ?? 'content';
        $author   = $item->author;
        $editUrl  = \Contensio\Http\Controllers\Admin\ReviewController::editUrl($item);
        $since    = $item->review_requested_at?->diffForHumans() ?? $item->created_at->diffForHumans();
    @endphp

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden"
         x-data="{ rejectOpen: false }">

        <div class="flex items-start gap-4 px-5 py-4">

            {{-- Type icon --}}
            <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ $editUrl }}"
                           class="text-base font-semibold text-gray-900 hover:text-blue-600 transition-colors truncate block">
                            {{ $title }}
                        </a>
                        <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                            <span class="capitalize">{{ $type }}</span>
                            <span class="text-gray-300">·</span>
                            @if($author)
                            <span>by {{ $author->name }}</span>
                            <span class="text-gray-300">·</span>
                            @endif
                            <span>{{ $since }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Approve --}}
                        <form method="POST" action="{{ route('contensio.account.reviews.approve', $item->id) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white
                                           text-sm font-semibold px-3.5 py-2 rounded-md transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Approve
                            </button>
                        </form>

                        {{-- Reject (toggle) --}}
                        <button type="button" @click="rejectOpen = !rejectOpen"
                                class="inline-flex items-center gap-1.5 border border-red-200 text-red-600 hover:bg-red-50
                                       text-sm font-semibold px-3.5 py-2 rounded-md transition-colors"
                                :class="rejectOpen ? 'bg-red-50' : 'bg-white'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject
                        </button>

                        {{-- Open in editor --}}
                        <a href="{{ $editUrl }}"
                           class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500
                                  hover:text-gray-900 border border-gray-200 hover:border-gray-300
                                  bg-white hover:bg-gray-50 rounded px-2.5 py-2 transition-colors"
                           title="Open in editor">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- Reject panel (inline, below the row) --}}
        <div x-show="rejectOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-red-100 bg-red-50 px-5 py-4">

            <form method="POST" action="{{ route('contensio.account.reviews.reject', $item->id) }}"
                  x-data="{ rejectType: 'soft_rejected' }">
                @csrf

                <p class="text-sm font-semibold text-red-800 mb-3">Reject: "{{ $title }}"</p>

                {{-- Rejection type --}}
                <div class="flex items-center gap-4 mb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="reject_type" value="soft_rejected"
                               x-model="rejectType"
                               class="w-4 h-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                        <span class="text-sm text-gray-700">
                            <strong>Request revision</strong>
                            <span class="text-gray-400 font-normal"> — author can edit and resubmit</span>
                        </span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="reject_type" value="hard_rejected"
                               x-model="rejectType"
                               class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                        <span class="text-sm text-gray-700">
                            <strong>Permanently reject</strong>
                            <span class="text-gray-400 font-normal"> — cannot be resubmitted</span>
                        </span>
                    </label>
                </div>

                {{-- Notes --}}
                <textarea name="notes" required maxlength="1000" rows="3"
                          placeholder="Explain what needs to change, or why this was rejected…"
                          class="w-full rounded-md border border-red-200 bg-white px-3 py-2 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent
                                 resize-none mb-3"></textarea>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white
                                   text-sm font-semibold px-4 py-2 rounded-md transition-colors shadow-sm"
                            :class="rejectType === 'hard_rejected' ? 'bg-red-700 hover:bg-red-800' : ''">
                        <span x-text="rejectType === 'hard_rejected' ? 'Permanently Reject' : 'Request Revision'"></span>
                    </button>
                    <button type="button" @click="rejectOpen = false"
                            class="text-sm text-red-600 hover:text-red-800 px-2 py-2">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

    </div>
    @endforeach
</div>

@endif

@endsection
