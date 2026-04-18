{{--
 | Contensio - The open content platform for Laravel.
 | Frontend — single comment item (recursive for threading).
 | https://contensio.com
 |
 | Variables: $comment, $depth (0 = top-level)
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@php
    $authorName    = $comment->author?->name ?? $comment->author_name ?? 'Anonymous';
    $authorAvatar  = $comment->author?->avatar_path ?? null;
    $authorCode    = $comment->author?->code ?? null;
    $initial       = strtoupper(substr($authorName, 0, 1));
@endphp

<div @class(['flex gap-4', 'ml-10' => $depth > 0, 'mt-4' => $depth > 0])>

    {{-- Avatar --}}
    <div class="shrink-0">
        @if($authorCode)
        <a href="{{ route('contensio.author', $authorCode) }}" class="block">
        @endif
        @if($authorAvatar)
        <img src="{{ asset('storage/' . $authorAvatar) }}"
             alt="{{ $authorName }}"
             class="w-10 h-10 rounded-full object-cover">
        @else
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-400 to-slate-600
                    flex items-center justify-center text-white font-bold">
            {{ $initial }}
        </div>
        @endif
        @if($authorCode)
        </a>
        @endif
    </div>

    {{-- Body --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-baseline gap-3 mb-2">
            @if($authorCode)
            <a href="{{ route('contensio.author', $authorCode) }}"
               class="font-semibold text-gray-900 hover:text-blue-600 transition-colors">{{ $authorName }}</a>
            @else
            <span class="font-semibold text-gray-900">{{ $authorName }}</span>
            @endif
            <time class="text-gray-400" datetime="{{ $comment->created_at->toDateString() }}">
                {{ $comment->created_at->diffForHumans() }}
            </time>
        </div>
        <div class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $comment->body }}</div>

        {{-- Reply link (only one level deep) --}}
        @if($depth === 0 && $commentsEnabled && $content->allow_comments)
        <div x-data="{ open: false }" class="mt-3">
            <button type="button" @click="open = !open"
                    class="font-medium text-gray-400 hover:text-gray-700 transition-colors">
                Reply
            </button>

            <div x-show="open" x-cloak class="mt-4">
                <form method="POST" action="{{ route('contensio.comments.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="content_id" value="{{ $content->id }}">
                    <input type="hidden" name="parent_id"  value="{{ $comment->id }}">

                    @auth
                    <div class="flex items-center gap-2 text-gray-500">
                        @if(auth()->user()->avatar_path)
                        <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}"
                             class="w-6 h-6 rounded-full object-cover shrink-0" alt="">
                        @else
                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-slate-400 to-slate-600
                                    flex items-center justify-center text-white font-bold shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        @endif
                        Replying as <strong class="text-gray-700">{{ auth()->user()->name }}</strong>
                    </div>
                    @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input type="text" name="author_name" placeholder="Your name *" required
                               class="rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-transparent">
                        <input type="email" name="author_email" placeholder="Your email *" required
                               class="rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-transparent">
                    </div>
                    @endauth

                    <textarea name="body" rows="3" required placeholder="Write a reply…"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 resize-none
                                     focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-transparent"></textarea>
                    <div class="flex items-center gap-3">
                        <button type="submit"
                                class="inline-flex items-center bg-gray-900 hover:bg-gray-700 text-white
                                       font-semibold px-5 py-2 rounded-lg transition-colors">
                            Post Reply
                        </button>
                        <button type="button" @click="open = false"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Nested replies --}}
        @if($comment->replies->isNotEmpty())
        <div class="mt-4 space-y-4">
            @foreach($comment->replies as $reply)
            @include('contensio::frontend.partials.comment-item', ['comment' => $reply, 'depth' => 1])
            @endforeach
        </div>
        @endif
    </div>

</div>
