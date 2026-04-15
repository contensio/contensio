{{--
 | Contensio - The open content platform for Laravel.
 | Auth — shared layout head/body open.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

{{--
    Shared auth page chrome. Each auth view extends this via @extends('cms::auth.partials.layout')
    and fills two sections:
        @section('title', 'Forgot password')
        @section('card')  ... @endsection
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Contensio') — {{ config('cms.name', 'Contensio') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="flex items-center justify-center gap-2.5 mb-8">
            <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-gray-900 font-semibold text-lg">{{ config('cms.name', 'Contensio') }}</span>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            @yield('card')
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            {!! __('cms::auth.powered_by', ['name' => '<a href="https://contensio.com" class="hover:text-gray-600">Contensio</a>']) !!}
        </p>

    </div>

</body>
</html>
