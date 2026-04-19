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

@php
    $adminName    = \Contensio\Services\WhitelabelService::adminName();
    $loginBgColor = \Contensio\Services\WhitelabelService::loginBgColor();
    $loginBgImage = \Contensio\Services\WhitelabelService::loginBgImageUrl();
    $loginTagline = \Contensio\Services\WhitelabelService::loginTagline();
    $loginStyle   = 'background-color: ' . e($loginBgColor) . ';';
    if ($loginBgImage) {
        $loginStyle .= ' background-image: url(\'' . e($loginBgImage) . '\'); background-size: cover; background-position: center;';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $adminName) — {{ $adminName }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="{{ \Contensio\Services\WhitelabelService::adminFaviconUrl() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="{{ $loginStyle }}">

    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="flex items-center justify-center mb-3">
            <a href="{{ url('/') }}" class="inline-flex items-center">
                <img src="{{ \Contensio\Services\WhitelabelService::adminLogoUrl() }}"
                     alt="{{ \Contensio\Services\WhitelabelService::adminName() }}">
            </a>
        </div>

        {{-- Optional tagline --}}
        @if($loginTagline)
        <p class="text-center text-sm text-gray-600 mb-6">{{ $loginTagline }}</p>
        @else
        <div class="mb-6"></div>
        @endif

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            @yield('card')
        </div>

        @if(! \Contensio\Services\WhitelabelService::hidePoweredBy())
        <p class="text-center text-sm text-gray-500 mt-6">
            {!! __('contensio::auth.powered_by', ['name' => '<a href="https://contensio.com" target="_blank" rel="noopener" class="font-medium hover:text-gray-800">Contensio</a>']) !!}
        </p>
        @endif

    </div>

</body>
</html>
