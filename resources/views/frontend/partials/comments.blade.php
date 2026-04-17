{{--
 | Contensio - The open content platform for Laravel.
 | Frontend — comments partial (threaded list + submission form).
 | https://contensio.com
 |
 | Variables expected:
 |   $content        — Content model
 |   $comments       — Collection of approved top-level comments (with replies eager-loaded)
 |   $commentsEnabled — bool
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@if($commentsEnabled && $content->allow_comments)

<section class="max-w-3xl mx-auto px-4 sm:px-6 pb-16">
    <hr class="border-gray-100 mb-10">

    {{-- Heading --}}
    @php $totalComments = $comments->sum(fn($c) => 1 + $c->replies->count()); @endphp
    <h2 class="text-xl font-bold text-gray-900 mb-8">
        {{ $totalComments > 0 ? $totalComments . ' ' . Str::plural('Comment', $totalComments) : 'Comments' }}
    </h2>

    {{-- Flash messages --}}
    @if(session('comment_success'))
    <div class="mb-6 flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 mt-0.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('comment_success') }}
    </div>
    @endif
    @if(session('comment_error'))
    <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 mt-0.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        {{ session('comment_error') }}
    </div>
    @endif

    {{-- Comments list --}}
    @if($comments->isNotEmpty())
    <div class="space-y-6 mb-10">
        @foreach($comments as $comment)
        @include('contensio::frontend.partials.comment-item', ['comment' => $comment, 'depth' => 0])
        @endforeach
    </div>
    @endif

    {{-- Submission form --}}
    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-6 sm:p-8">
        <h3 class="text-base font-bold text-gray-900 mb-5">Leave a comment</h3>

        <form method="POST" action="{{ route('contensio.comments.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="content_id" value="{{ $content->id }}">

            {{-- Logged-in identity / guest fields --}}
            @auth
            <div class="flex items-center gap-3 py-2.5 px-3 bg-white border border-gray-200 rounded-lg text-sm text-gray-600">
                @if(auth()->user()->avatar_path)
                <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}"
                     class="w-7 h-7 rounded-full object-cover shrink-0" alt="">
                @else
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-slate-400 to-slate-600
                            flex items-center justify-center text-white text-xs font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                @endif
                <span>Commenting as <strong class="text-gray-900">{{ auth()->user()->name }}</strong></span>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="author_name" value="{{ old('author_name') }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-transparent">
                    @error('author_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="author_email" value="{{ old('author_email') }}" required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-400">Your email won't be published.</p>
                    @error('author_email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            @endauth

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Comment <span class="text-red-500">*</span></label>
                <textarea name="body" rows="5" required
                          placeholder="Write your comment…"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm resize-y
                                 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:border-transparent">{{ old('body') }}</textarea>
                @error('body')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-700 text-white
                               font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                    Post Comment
                </button>
            </div>
        </form>
    </div>

</section>

@endif
