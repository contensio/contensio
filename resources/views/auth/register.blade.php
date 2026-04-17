{{--
 | Contensio - The open content platform for Laravel.
 | Auth — registration.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::auth.partials.layout')
@section('title', 'Create account')

@section('card')

    @if($disabled)

    {{-- Registration disabled --}}
    <div class="flex flex-col items-center text-center py-2">
        <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <h1 class="text-lg font-bold text-gray-900 mb-2">Registration is closed</h1>
        <p class="text-sm text-gray-500 leading-relaxed mb-6">
            New account registration is currently disabled.<br>Please contact the site administrator.
        </p>
        <a href="{{ route('contensio.login') }}"
           class="text-sm font-medium text-[#d04a1f] hover:text-[#b23e18]">
            ← Back to login
        </a>
    </div>

    @else

    <h1 class="text-xl font-bold text-gray-900 mb-1">Create account</h1>
    <p class="text-sm text-gray-500 mb-6">Fill in the details below to get started.</p>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5">
        <p class="text-sm text-red-700">{{ session('error') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5">
        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('contensio.register.store') }}">
        @csrf

        <div class="space-y-4">

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                           @error('name') border-red-400 @enderror"
                    placeholder="Your name">
            </div>

            {{-- Username --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                <div class="flex rounded-lg border border-gray-300 overflow-hidden
                            focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent
                            @error('username') border-red-400 @enderror">
                    <span class="inline-flex items-center px-3 bg-gray-50 border-r border-gray-300
                                 text-gray-400 text-sm select-none">@</span>
                    <input type="text" id="username-input" name="username"
                           value="{{ old('username') }}"
                           required minlength="5" maxlength="25"
                           autocomplete="username"
                           placeholder="your_username"
                           class="flex-1 px-3 py-2.5 text-sm text-gray-900 outline-none bg-white">
                </div>
                <p id="username-hint" class="mt-1.5 text-xs text-gray-400">
                    Letters, numbers and underscores only · 5–25 characters
                </p>
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                           @error('email') border-red-400 @enderror"
                    placeholder="you@example.com">
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input type="password" name="password" required minlength="8"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="At least 8 characters">
            </div>

            {{-- Confirm password --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

        </div>

        <button type="submit"
            class="w-full mt-6 bg-[#d04a1f] hover:bg-[#b23e18] text-white font-medium text-sm
                   py-2.5 rounded-lg transition-colors">
            Create account
        </button>

        <p class="mt-5 text-center text-sm text-gray-500">
            Already have an account?
            <a href="{{ route('contensio.login') }}" class="font-medium text-[#d04a1f] hover:text-[#b23e18]">Sign in</a>
        </p>
    </form>

    <script>
    (function () {
        var input = document.getElementById('username-input');
        var hint  = document.getElementById('username-hint');
        if (!input) return;

        function sanitize(val) {
            return val.toLowerCase().replace(/[^a-z0-9_]/g, '');
        }

        input.addEventListener('input', function () {
            var start = this.selectionStart;
            var end   = this.selectionEnd;
            var clean = sanitize(this.value);

            if (this.value !== clean) {
                // Restore cursor after value replacement
                var removed = this.value.length - clean.length;
                this.value = clean;
                start = Math.max(0, start - removed);
                end   = Math.max(0, end   - removed);
                this.setSelectionRange(start, end);
            }

            if (clean.length >= 5) {
                hint.textContent = '@' + clean;
                hint.style.color = '#16a34a'; // green-600
            } else if (clean.length > 0) {
                hint.textContent = '@' + clean + ' · ' + (5 - clean.length) + ' more character' + (5 - clean.length === 1 ? '' : 's') + ' needed';
                hint.style.color = '#d97706'; // amber-600
            } else {
                hint.textContent = 'Letters, numbers and underscores only · 5–25 characters';
                hint.style.color = '';
            }
        });

        // Run once on page load to handle old() values
        if (input.value) input.dispatchEvent(new Event('input'));
    })();
    </script>

    @endif

@endsection
