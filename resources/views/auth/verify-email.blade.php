{{--
 | Contensio - The open content platform for Laravel.
 | Auth — email verification notice.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::auth.partials.layout')
@section('title', 'Verify your email')

@section('card')

    <div class="text-center mb-5">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 text-blue-600 mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-1">Verify your email</h1>
        <p class="text-sm text-gray-500">Check your inbox for the verification link.</p>
    </div>

    @if(session('status') === 'verification-link-sent')
    <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 mb-5">
        <p class="text-sm text-green-700">A fresh verification link has been sent to your email address.</p>
    </div>
    @endif

    <p class="text-sm text-gray-600 leading-relaxed mb-5">
        Before you can continue, please verify your email using the link we just sent you.
        Didn't get the email?
    </p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit"
            class="w-full bg-[#d04a1f] hover:bg-[#b23e18] text-white font-medium text-sm py-2.5 rounded-lg transition-colors">
            Resend verification email
        </button>
    </form>

    <form method="POST" action="{{ route('contensio.logout') }}" class="mt-3">
        @csrf
        <button type="submit"
            class="w-full text-sm text-gray-500 hover:text-gray-700 font-medium py-2">
            Sign out
        </button>
    </form>

@endsection
