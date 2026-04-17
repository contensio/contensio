{{--
 | Contensio - The open content platform for Laravel.
 | Admin — comments list.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Comments')

@section('breadcrumb')
    <span class="text-gray-900 font-medium">Comments</span>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-3">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-3">
    {{ session('error') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Comments</h1>
        <p class="text-sm text-gray-400 mt-0.5">Review and moderate reader comments.</p>
    </div>
</div>

{{-- Active content filter badge --}}
@if($filterContent)
<div class="mb-4 flex items-center gap-2 text-sm">
    <span class="text-gray-500">Filtered by:</span>
    <span class="inline-flex items-center gap-1.5 bg-blue-50 border border-blue-200 text-blue-700 px-3 py-1 rounded-full font-medium">
        {{ $filterContent->translations->first()?->title ?? 'Post #' . $filterContent->id }}
        <a href="{{ route('contensio.account.comments.index', ['status' => $status]) }}"
           class="text-blue-400 hover:text-blue-700 ml-0.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </a>
    </span>
</div>
@endif

{{-- Status tabs --}}
<div class="flex items-center gap-1 mb-4 border-b border-gray-200 overflow-x-auto">
    @php
        $tabs = [
            'pending'  => 'Pending',
            'approved' => 'Approved',
            'spam'     => 'Spam',
            'trashed'  => 'Trashed',
            'all'      => 'All',
        ];
    @endphp
    @foreach($tabs as $tabKey => $tabLabel)
    @php $tabCount = $counts[$tabKey] ?? 0; @endphp
    <a href="{{ route('contensio.account.comments.index', array_filter(['status' => $tabKey, 'content_id' => $contentId, 'q' => $search])) }}"
       class="shrink-0 inline-flex items-center gap-1.5 px-3 pb-3 pt-1 text-sm font-medium border-b-2 transition-colors
              {{ $status === $tabKey
                  ? 'border-ember-500 text-ember-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
        {{ $tabLabel }}
        @if($tabCount > 0)
        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-semibold
                     {{ $status === $tabKey ? 'bg-ember-100 text-ember-700' : 'bg-gray-100 text-gray-600' }}">
            {{ $tabCount }}
        </span>
        @endif
    </a>
    @endforeach
</div>

{{-- Search bar --}}
<form method="GET" action="{{ route('contensio.account.comments.index') }}" class="mb-4 flex gap-2">
    @if($contentId)
    <input type="hidden" name="content_id" value="{{ $contentId }}">
    @endif
    <input type="hidden" name="status" value="{{ $status }}">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search comments, names, emails…"
               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
    </div>
    <button type="submit"
            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        Search
    </button>
    @if($search)
    <a href="{{ route('contensio.account.comments.index', array_filter(['status' => $status, 'content_id' => $contentId])) }}"
       class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
        Clear
    </a>
    @endif
</form>

@if($comments->isEmpty())

<div class="bg-white border border-gray-200 rounded-lg p-16 text-center">
    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
    </div>
    <h3 class="text-sm font-semibold text-gray-900 mb-1">No comments found</h3>
    <p class="text-sm text-gray-400">
        @if($search)
            No comments match your search.
        @else
            There are no {{ $status !== 'all' ? $status : '' }} comments yet.
        @endif
    </p>
</div>

@else

{{-- Per-comment action forms — rendered OUTSIDE the bulk form to avoid nested forms --}}
@foreach($comments as $comment)
    @if($comment->status !== 'approved')
    <form id="approve-{{ $comment->id }}" method="POST" action="{{ route('contensio.account.comments.approve', $comment->id) }}" class="hidden">@csrf</form>
    @endif
    @if($comment->status !== 'spam')
    <form id="spam-{{ $comment->id }}" method="POST" action="{{ route('contensio.account.comments.spam', $comment->id) }}" class="hidden">@csrf</form>
    @endif
    @if($comment->status !== 'trashed' && $comment->status !== 'spam')
    <form id="trash-{{ $comment->id }}" method="POST" action="{{ route('contensio.account.comments.trash', $comment->id) }}" class="hidden">@csrf</form>
    @endif
    @if(in_array($comment->status, ['spam', 'trashed']))
    <form id="restore-{{ $comment->id }}" method="POST" action="{{ route('contensio.account.comments.restore', $comment->id) }}" class="hidden">@csrf</form>
    @endif
    <form id="delete-{{ $comment->id }}" method="POST" action="{{ route('contensio.account.comments.destroy', $comment->id) }}" class="hidden">@csrf @method('DELETE')</form>
@endforeach

{{-- Bulk action form --}}
<form id="bulk-form" method="POST" action="{{ route('contensio.account.comments.bulk') }}">
    @csrf
    <input type="hidden" name="action" id="bulk-action-input">

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden"
         x-data="{
             noneSelected: false,
             confirmBulk(action, title, description, confirmLabel) {
                 const checked = document.querySelectorAll('.row-check:checked');
                 if (checked.length === 0) {
                     this.noneSelected = true;
                     setTimeout(() => this.noneSelected = false, 2500);
                     return;
                 }
                 this.noneSelected = false;
                 document.getElementById('bulk-action-input').value = action;
                 $dispatch('cms:confirm', { title, description, confirmLabel, formId: 'bulk-form' });
             }
         }">

        {{-- Bulk toolbar --}}
        <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-100 bg-gray-50/50">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" id="select-all"
                       class="w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500"
                       @change="document.querySelectorAll('.row-check').forEach(cb => cb.checked = $event.target.checked)">
                <span class="text-xs font-medium text-gray-500">Select all</span>
            </label>
            <span x-show="noneSelected" x-cloak
                  x-transition:enter="transition ease-out duration-150"
                  x-transition:enter-start="opacity-0 -translate-y-1"
                  x-transition:enter-end="opacity-100 translate-y-0"
                  x-transition:leave="transition ease-in duration-150"
                  x-transition:leave-start="opacity-100"
                  x-transition:leave-end="opacity-0"
                  class="text-xs text-amber-600 font-medium">
                Select at least one comment first.
            </span>
            <div class="flex items-center gap-1 ml-2">
                @if($status !== 'approved')
                <button type="button"
                        @click="confirmBulk('approve', 'Approve selected comments?', 'The selected comments will be approved and made visible.', 'Approve')"
                        class="text-xs font-medium text-gray-600 hover:text-green-700 border border-gray-200 hover:border-green-300 bg-white hover:bg-green-50 rounded px-2.5 py-1 transition-colors">
                    Approve
                </button>
                @endif
                @if($status !== 'spam')
                <button type="button"
                        @click="confirmBulk('spam', 'Mark selected as spam?', 'The selected comments will be moved to spam.', 'Mark Spam')"
                        class="text-xs font-medium text-gray-600 hover:text-amber-700 border border-gray-200 hover:border-amber-300 bg-white hover:bg-amber-50 rounded px-2.5 py-1 transition-colors">
                    Mark Spam
                </button>
                @endif
                @if($status !== 'trashed')
                <button type="button"
                        @click="confirmBulk('trash', 'Trash selected comments?', 'The selected comments will be moved to trash.', 'Move to Trash')"
                        class="text-xs font-medium text-gray-600 hover:text-gray-900 border border-gray-200 hover:border-gray-300 bg-white hover:bg-gray-100 rounded px-2.5 py-1 transition-colors">
                    Trash
                </button>
                @endif
                @if(in_array($status, ['spam', 'trashed']))
                <button type="button"
                        @click="confirmBulk('restore', 'Restore selected comments?', 'The selected comments will be restored to pending.', 'Restore')"
                        class="text-xs font-medium text-gray-600 hover:text-blue-700 border border-gray-200 hover:border-blue-300 bg-white hover:bg-blue-50 rounded px-2.5 py-1 transition-colors">
                    Restore
                </button>
                @endif
                <button type="button"
                        @click="confirmBulk('delete', 'Delete selected comments?', 'The selected comments will be permanently deleted and cannot be recovered.', 'Delete')"
                        class="text-xs font-medium text-red-600 hover:text-red-800 border border-red-100 hover:border-red-300 bg-white hover:bg-red-50 rounded px-2.5 py-1 transition-colors">
                    Delete
                </button>
            </div>
        </div>

        {{-- Comment rows --}}
        <div class="divide-y divide-gray-100">
            @foreach($comments as $comment)
            @php
                $commentAuthorName  = $comment->author?->name  ?? $comment->author_name  ?? 'Anonymous';
                $commentAuthorEmail = $comment->author?->email ?? $comment->author_email ?? null;
                $contentTitle = $comment->content?->translations->first()?->title ?? 'Post #' . $comment->content_id;
            @endphp
            <div class="flex gap-3 px-4 py-4 hover:bg-gray-50/60 transition-colors">

                {{-- Checkbox --}}
                <div class="shrink-0 pt-0.5">
                    <input type="checkbox" name="ids[]" value="{{ $comment->id }}"
                           class="row-check w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500">
                </div>

                {{-- Avatar --}}
                <div class="shrink-0">
                    @if($comment->author?->avatar_path)
                    <img src="{{ asset('storage/' . str_replace('avatars/', 'avatars/thumb_', $comment->author->avatar_path)) }}"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                         class="w-9 h-9 rounded-full object-cover" alt="">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-slate-400 to-slate-600
                                items-center justify-center text-white text-sm font-bold hidden">
                        {{ strtoupper(substr($commentAuthorName, 0, 1)) }}
                    </div>
                    @else
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-slate-400 to-slate-600
                                flex items-center justify-center text-white text-sm font-bold">
                        {{ strtoupper(substr($commentAuthorName, 0, 1)) }}
                    </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 mb-1">
                        @if($comment->author?->code)
                        <a href="{{ route('contensio.author', $comment->author->code) }}"
                           target="_blank"
                           class="font-semibold text-gray-900 text-sm hover:text-blue-600 transition-colors">{{ $commentAuthorName }}</a>
                        @else
                        <span class="font-semibold text-gray-900 text-sm">{{ $commentAuthorName }}</span>
                        @endif
                        @if($commentAuthorEmail)
                        <span class="text-xs text-gray-400">{{ $commentAuthorEmail }}</span>
                        @endif
                        <span class="text-xs text-gray-400">·</span>
                        <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                        @if($comment->parent_id)
                        <span class="text-xs text-gray-400">· <em>reply</em></span>
                        @endif
                    </div>

                    <p class="text-sm text-gray-700 leading-relaxed mb-2 line-clamp-3">{{ $comment->body }}</p>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        {{-- Post link --}}
                        <a href="{{ route('contensio.account.comments.index', ['content_id' => $comment->content_id, 'status' => 'all']) }}"
                           class="text-xs text-gray-400 hover:text-blue-600 transition-colors truncate max-w-xs">
                            <svg class="w-3 h-3 inline-block mr-0.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ $contentTitle }}
                        </a>

                        {{-- Status badge --}}
                        @if($status === 'all')
                        <span @class([
                            'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold',
                            'bg-amber-50 text-amber-700 border border-amber-200' => $comment->status === 'pending',
                            'bg-green-50 text-green-700 border border-green-200'  => $comment->status === 'approved',
                            'bg-red-50 text-red-700 border border-red-200'        => $comment->status === 'spam',
                            'bg-gray-100 text-gray-600 border border-gray-200'    => $comment->status === 'trashed',
                        ])>
                            {{ ucfirst($comment->status) }}
                        </span>
                        @endif

                        {{-- Quick actions --}}
                        <div class="flex items-center gap-1 ml-auto">
                            @if($comment->status !== 'approved')
                            <button type="button"
                                    @click="$dispatch('cms:confirm', { title: 'Approve comment?', description: 'This comment will be approved and made visible on the site.', confirmLabel: 'Approve', formId: 'approve-{{ $comment->id }}' })"
                                    class="text-xs font-medium text-gray-500 hover:text-green-700 border border-gray-200 hover:border-green-300 bg-white hover:bg-green-50 rounded px-2.5 py-1 transition-colors">
                                Approve
                            </button>
                            @endif
                            @if($comment->status !== 'spam')
                            <button type="button"
                                    @click="$dispatch('cms:confirm', { title: 'Mark as spam?', description: 'This comment will be moved to the spam folder.', confirmLabel: 'Mark Spam', formId: 'spam-{{ $comment->id }}' })"
                                    class="text-xs font-medium text-gray-500 hover:text-amber-700 border border-gray-200 hover:border-amber-300 bg-white hover:bg-amber-50 rounded px-2.5 py-1 transition-colors">
                                Spam
                            </button>
                            @endif
                            @if($comment->status !== 'trashed' && $comment->status !== 'spam')
                            <button type="button"
                                    @click="$dispatch('cms:confirm', { title: 'Move to trash?', description: 'This comment will be moved to trash.', confirmLabel: 'Move to Trash', formId: 'trash-{{ $comment->id }}' })"
                                    class="text-xs font-medium text-gray-500 hover:text-gray-900 border border-gray-200 hover:border-gray-300 bg-white hover:bg-gray-100 rounded px-2.5 py-1 transition-colors">
                                Trash
                            </button>
                            @endif
                            @if(in_array($comment->status, ['spam', 'trashed']))
                            <button type="button"
                                    @click="$dispatch('cms:confirm', { title: 'Restore comment?', description: 'This comment will be restored to pending and await your review.', confirmLabel: 'Restore', formId: 'restore-{{ $comment->id }}' })"
                                    class="text-xs font-medium text-gray-500 hover:text-blue-700 border border-gray-200 hover:border-blue-300 bg-white hover:bg-blue-50 rounded px-2.5 py-1 transition-colors">
                                Restore
                            </button>
                            @endif
                            <button type="button"
                                    @click="$dispatch('cms:confirm', { title: 'Delete comment?', description: 'This comment will be permanently deleted and cannot be recovered.', confirmLabel: 'Delete', formId: 'delete-{{ $comment->id }}' })"
                                    class="text-xs font-medium text-red-500 hover:text-red-700 border border-red-100 hover:border-red-300 bg-white hover:bg-red-50 rounded px-2.5 py-1 transition-colors">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</form>

{{-- Pagination --}}
@if($comments->hasPages())
<div class="mt-4">
    {{ $comments->links() }}
</div>
@endif

@endif

@endsection

