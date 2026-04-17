{{--
 | Contensio - The open content platform for Laravel.
 | Admin — comments settings.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Comment Settings')

@section('breadcrumb')
<a href="{{ route('contensio.account.settings.index') }}" class="text-gray-400 hover:text-gray-700">Configuration</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Comments</span>
@endsection

@section('content')

<div class="max-w-3xl mx-auto">

    <h1 class="text-xl font-bold text-gray-900 mb-1">Comment Settings</h1>
    <p class="text-sm text-gray-500 mb-5">Control how comments work across your site.</p>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('contensio.account.settings.comments.save') }}" class="space-y-4">
        @csrf

        {{-- Global toggle --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-base font-bold text-gray-900 mb-1">Global setting</h2>
            <p class="text-xs text-gray-500 mb-4">Turn comments on or off for the entire site.</p>

            <label class="flex items-start gap-2.5 cursor-pointer group">
                <input type="hidden" name="comments_enabled" value="0">
                <input type="checkbox" name="comments_enabled" value="1"
                       {{ ! empty($settings['comments_enabled']) ? 'checked' : '' }}
                       class="mt-0.5 w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500">
                <div>
                    <span class="text-sm font-medium text-gray-800">Enable comments</span>
                    <p class="text-xs text-gray-500 mt-0.5">When disabled, the comment form is hidden everywhere and no new comments can be submitted.</p>
                </div>
            </label>
        </div>

        {{-- Moderation --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 mb-1">Moderation</h2>
                <p class="text-xs text-gray-500">Control who can comment and whether comments need approval.</p>
            </div>

            <label class="flex items-start gap-2.5 cursor-pointer">
                <input type="hidden" name="comments_require_approval" value="0">
                <input type="checkbox" name="comments_require_approval" value="1"
                       {{ ! empty($settings['comments_require_approval']) ? 'checked' : '' }}
                       class="mt-0.5 w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500">
                <div>
                    <span class="text-sm font-medium text-gray-800">Hold new comments for moderation</span>
                    <p class="text-xs text-gray-500 mt-0.5">All new comments will be set to <strong>pending</strong> and won't appear until manually approved.</p>
                </div>
            </label>

            <label class="flex items-start gap-2.5 cursor-pointer">
                <input type="hidden" name="comments_allow_guests" value="0">
                <input type="checkbox" name="comments_allow_guests" value="1"
                       {{ ! empty($settings['comments_allow_guests']) ? 'checked' : '' }}
                       class="mt-0.5 w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500">
                <div>
                    <span class="text-sm font-medium text-gray-800">Allow guest comments</span>
                    <p class="text-xs text-gray-500 mt-0.5">Visitors who are not logged in can leave comments with just a name and email.</p>
                </div>
            </label>
        </div>

        {{-- Auto-close --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
            <div>
                <h2 class="text-base font-bold text-gray-900 mb-1">Auto-close</h2>
                <p class="text-xs text-gray-500">Automatically disable commenting on older posts.</p>
            </div>

            <div class="flex items-center gap-3">
                <label class="text-sm text-gray-700 shrink-0">Close comments after</label>
                <input type="number" name="comments_close_after_days"
                       value="{{ old('comments_close_after_days', $settings['comments_close_after_days'] ?? 0) }}"
                       min="0" max="3650"
                       class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm text-center
                              focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                <label class="text-sm text-gray-700 shrink-0">days</label>
            </div>
            <p class="text-xs text-gray-500">Set to <code class="bg-gray-100 px-1 rounded">0</code> to never auto-close.</p>
        </div>

        <div class="flex items-center justify-between text-sm">
            <a href="{{ route('contensio.account.comments.index') }}"
               class="text-gray-500 hover:text-gray-800 inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                View all comments
            </a>

            <button type="submit"
                    class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                Save Changes
            </button>
        </div>
    </form>

</div>

@endsection
