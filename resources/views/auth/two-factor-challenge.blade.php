{{--
 | Contensio - The open content platform for Laravel.
 | Auth — two-factor authentication challenge (after login).
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::auth.partials.layout')
@section('title', 'Two-factor authentication')

@section('card')

<div x-data="{ mode: 'code' }">

    <h1 class="text-xl font-bold text-gray-900 mb-1">Two-factor authentication</h1>
    <p x-show="mode === 'code'" class="text-sm text-gray-500 mb-5">
        Enter the 6-digit code from your authenticator app.
    </p>
    <p x-show="mode === 'recovery'" x-cloak class="text-sm text-gray-500 mb-5">
        Enter one of your recovery codes to sign in.
    </p>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5">
        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
    </div>
    @endif

    {{-- TOTP code form --}}
    <form method="POST" action="{{ route('two-factor.login') }}" x-show="mode === 'code'">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Authentication code</label>
            <input type="text" name="code" autofocus required inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code"
                maxlength="6"
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-center text-lg tracking-[0.5em] font-mono
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="••••••">
        </div>

        <button type="submit"
            class="w-full mt-5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm py-2.5 rounded-lg transition-colors">
            Verify code
        </button>
    </form>

    {{-- Recovery code form --}}
    <form method="POST" action="{{ route('two-factor.login') }}" x-show="mode === 'recovery'" x-cloak>
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Recovery code</label>
            <input type="text" name="recovery_code" required autocomplete="one-time-code"
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm font-mono
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="one-of-your-recovery-codes">
        </div>

        <button type="submit"
            class="w-full mt-5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm py-2.5 rounded-lg transition-colors">
            Sign in
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        <template x-if="mode === 'code'">
            <button type="button" @click="mode = 'recovery'" class="text-blue-600 hover:text-blue-700 font-medium">
                Use a recovery code instead
            </button>
        </template>
        <template x-if="mode === 'recovery'">
            <button type="button" @click="mode = 'code'" class="text-blue-600 hover:text-blue-700 font-medium">
                Use an authenticator code instead
            </button>
        </template>
    </p>

    <form method="POST" action="{{ route('cms.logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="w-full text-xs text-gray-400 hover:text-gray-600 py-2">
            Cancel and sign out
        </button>
    </form>

</div>

<style>[x-cloak] { display: none !important; }</style>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

@endsection
