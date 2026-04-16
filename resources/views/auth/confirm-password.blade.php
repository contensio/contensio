{{--
 | Contensio - The open content platform for Laravel.
 | Auth — confirm password (for sensitive admin actions).
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::auth.partials.layout')
@section('title', 'Confirm password')

@section('card')

    <h1 class="text-xl font-bold text-gray-900 mb-1">Confirm your password</h1>
    <p class="text-sm text-gray-500 mb-6">Please re-enter your password to continue. This is a sensitive action.</p>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5">
        <p class="text-sm text-red-700">{{ $errors->first() }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <input type="password" name="password" autofocus required autocomplete="current-password"
                class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <button type="submit"
            class="w-full mt-6 bg-[#d04a1f] hover:bg-[#b23e18] text-white font-medium text-sm
                   py-2.5 rounded-lg transition-colors">
            Confirm
        </button>
    </form>

@endsection
