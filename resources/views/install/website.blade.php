{{--
 | Contensio - The open content platform for Laravel.
 | Install — website.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::install.layout')
@php($currentStep = 2)

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('cms::install.website.title') }}</h1>
        <p class="text-gray-500 mt-1">{{ __('cms::install.website.subtitle') }}</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-700 text-sm">{{ $errors->first() }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('cms.install.website.store') }}" id="websiteForm">
        @csrf

        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('cms::install.website.site_name') }}</label>
                <input type="text" name="site_name" value="{{ old('site_name') }}"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="{{ __('cms::install.website.site_name_placeholder') }}" autofocus>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('cms::install.website.language') }}</label>
                <select name="language"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                    @foreach($languages as $code => $name)
                        <option value="{{ $code }}" {{ old('language', 'en') === $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-gray-400 text-xs mt-1.5">{{ __('cms::install.website.language_hint') }}</p>
            </div>
        </div>

        <div class="flex justify-between items-center mt-8">
            <a href="{{ route('cms.install.database') }}"
               class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('cms::install.buttons.back') }}
            </a>

            <button type="submit" id="continueBtn"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition-colors flex items-center gap-2">
                <svg id="spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ __('cms::install.buttons.continue') }}
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('websiteForm').addEventListener('submit', function() {
    document.getElementById('spinner').classList.remove('hidden');
    document.getElementById('continueBtn').disabled = true;
});
</script>
@endsection
