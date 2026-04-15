{{--
 | Contensio - The open content platform for Laravel.
 | Auth — forgot password (request reset link).
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::auth.partials.layout')
@section('title', 'Reset password')

@section('card')

    <h1 class="text-xl font-bold text-gray-900 mb-1">Reset your password</h1>
    <p class="text-sm text-gray-500 mb-6">Enter your email and we'll send you a link to set a new password.</p>

    @if(session('status'))
    <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 mb-5">
        <p class="text-sm text-green-700">{{ session('status') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5">
        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
            <input type="email" name="email" value="{{ old('email') }}" autofocus required
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                       @error('email') border-red-400 @enderror"
                placeholder="you@example.com">
        </div>

        <button type="submit"
            class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm
                   py-2.5 rounded-lg transition-colors">
            Send reset link
        </button>
    </form>

    <p class="text-sm text-gray-500 text-center mt-6">
        Remember your password?
        <a href="{{ route('cms.login') }}" class="text-blue-600 hover:text-blue-700 font-medium">Sign in</a>
    </p>

@endsection
