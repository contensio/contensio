{{--
 | Contensio - The open content platform for Laravel.
 | Auth — reset password (set new password from email link).
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::auth.partials.layout')
@section('title', 'Set new password')

@section('card')

    <h1 class="text-xl font-bold text-gray-900 mb-1">Set a new password</h1>
    <p class="text-sm text-gray-500 mb-6">Enter a new password for your account.</p>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5">
        @foreach($errors->all() as $err)
        <p class="text-sm text-red-700">{{ $err }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" required readonly
                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3.5 py-2.5 text-sm text-gray-600
                           focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
                <input type="password" name="password" autofocus required autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm new password</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <button type="submit"
            class="w-full mt-6 bg-[#d04a1f] hover:bg-[#b23e18] text-white font-medium text-sm
                   py-2.5 rounded-lg transition-colors">
            Update password
        </button>
    </form>

@endsection
