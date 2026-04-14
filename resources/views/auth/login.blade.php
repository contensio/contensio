{{--
 | Contensio - The open content platform for Laravel.
 | Auth — login.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('cms::auth.login.title') }} — {{ config('cms.name', 'Contensio') }}</title>
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

            <h1 class="text-xl font-bold text-gray-900 mb-1">{{ __('cms::auth.login.title') }}</h1>
            <p class="text-sm text-gray-500 mb-6">{{ __('cms::auth.login.subtitle') }}</p>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-5">
                <p class="text-sm text-red-700">{{ $errors->first() }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('cms.login.store') }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('cms::auth.login.email') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" autofocus required
                            class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('email') border-red-400 @enderror"
                            placeholder="{{ __('cms::auth.login.email_placeholder') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('cms::auth.login.password') }}</label>
                        <input type="password" name="password" required
                            class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm text-gray-900
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="{{ __('cms::auth.login.password_placeholder') }}">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            {{ __('cms::auth.login.remember') }}
                        </label>
                    </div>
                </div>

                <button type="submit"
                    class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm
                           py-2.5 rounded-lg transition-colors">
                    {{ __('cms::auth.login.submit') }}
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            {!! __('cms::auth.powered_by', ['name' => '<a href="https://contensio.com" class="hover:text-gray-600">Contensio</a>']) !!}
        </p>

    </div>

</body>
</html>
