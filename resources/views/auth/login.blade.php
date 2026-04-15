{{--
 | Contensio - The open content platform for Laravel.
 | Auth — login.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::auth.partials.layout')
@section('title', __('cms::auth.login.title'))

@section('card')

    <h1 class="text-xl font-bold text-gray-900 mb-1">{{ __('cms::auth.login.title') }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ __('cms::auth.login.subtitle') }}</p>

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
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-medium text-gray-700">{{ __('cms::auth.login.password') }}</label>
                    @if(\Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::resetPasswords()))
                    <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                        Forgot password?
                    </a>
                    @endif
                </div>
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

    {{-- Plugins (e.g. contensio/social-connect) can inject "Continue with X" buttons here --}}
    {!! \Contensio\Cms\Support\Hook::render('login.after_form') !!}

@endsection
