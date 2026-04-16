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
    Shared auth page chrome. Each auth view extends this via @extends('contensio::auth.partials.layout')
    and fills two sections:
        @section('title', 'Forgot password')
        @section('card')  ... @endsection
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Contensio') — {{ config('contensio.name', 'Contensio') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="{{ asset(config('contensio.admin_favicon', 'vendor/contensio/img/favicon128x128.png')) }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background-color: #fbf8f0;">

    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="flex items-center justify-center mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center">
                <img src="{{ asset(config('contensio.admin_logo', 'vendor/contensio/img/logo.png')) }}"
                     alt="{{ config('contensio.name', 'Contensio') }}">
            </a>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            @yield('card')
        </div>

        <p class="text-center text-sm text-gray-500 mt-6">
            {!! __('contensio::auth.powered_by', ['name' => '<a href="https://contensio.com" target="_blank" rel="noopener" class="font-medium hover:text-gray-800">Contensio</a>']) !!}
        </p>

    </div>

</body>
</html>
