{{--
 | Contensio - The open content platform for Laravel.
 | Install — account.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::install.layout')
@php($currentStep = 3)

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('contensio::install.account.title') }}</h1>
        <p class="text-gray-500 mt-1">{{ __('contensio::install.account.subtitle') }}</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-700 text-sm">{{ $errors->first() }}</p>
    </div>
    @endif

    <form method="POST" action="{{ route('contensio.install.account.store') }}" id="accountForm">
        @csrf

        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('contensio::install.account.name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="{{ __('contensio::install.account.name_placeholder') }}" autofocus>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('contensio::install.account.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="{{ __('contensio::install.account.email_placeholder') }}">
                <p class="text-gray-400 text-xs mt-1.5">{{ __('contensio::install.account.email_hint') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('contensio::install.account.password') }}</label>
                <input type="password" name="password" id="password"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="{{ __('contensio::install.account.password_placeholder') }}" oninput="checkStrength(this.value)">

                {{-- Strength meter --}}
                <div class="mt-2">
                    <div class="flex gap-1 mb-1">
                        <div id="s1" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-colors"></div>
                        <div id="s2" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-colors"></div>
                        <div id="s3" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-colors"></div>
                        <div id="s4" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-colors"></div>
                    </div>
                    <p id="strengthLabel" class="text-xs text-gray-400"></p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('contensio::install.account.confirm_password') }}</label>
                <input type="password" name="password_confirmation"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="{{ __('contensio::install.account.confirm_password_placeholder') }}">
            </div>
        </div>

        <div class="flex justify-between items-center mt-8">
            <a href="{{ route('contensio.install.website') }}"
               class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('contensio::install.buttons.back') }}
            </a>

            <button type="submit" id="installBtn"
                class="bg-ember-500 hover:bg-ember-600 text-white font-medium px-6 py-2.5 rounded-lg transition-colors flex items-center gap-2">
                <svg id="spinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ __('contensio::install.account.submit') }}
            </button>
        </div>
    </form>
</div>

<script>
<?php $strengthLabels = [
    1 => __('contensio::install.account.strength.weak'),
    2 => __('contensio::install.account.strength.fair'),
    3 => __('contensio::install.account.strength.good'),
    4 => __('contensio::install.account.strength.strong'),
]; ?>
const strengthLabels = {!! json_encode($strengthLabels, JSON_UNESCAPED_UNICODE) !!};
const installingText = {!! json_encode(__('contensio::install.account.installing'), JSON_UNESCAPED_UNICODE) !!};

function checkStrength(password) {
    let score = 0;
    if (password.length >= 8)  score++;
    if (password.length >= 12) score++;
    if (/[A-Z]/.test(password) && /[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    const colors = ['', 'bg-red-400', 'bg-yellow-400', 'bg-blue-500', 'bg-green-500'];

    for (let i = 1; i <= 4; i++) {
        const el = document.getElementById('s' + i);
        el.className = 'h-1.5 flex-1 rounded-full transition-colors ' + (i <= score ? colors[score] : 'bg-gray-200');
    }

    const labelEl = document.getElementById('strengthLabel');
    labelEl.textContent = password.length > 0 ? (strengthLabels[score] || '') : '';
    labelEl.className = 'text-xs ' + (score <= 1 ? 'text-red-500' : score === 2 ? 'text-yellow-500' : score === 3 ? 'text-blue-500' : 'text-green-600');
}

document.getElementById('accountForm').addEventListener('submit', function() {
    const btn = document.getElementById('installBtn');
    document.getElementById('spinner').classList.remove('hidden');
    btn.disabled = true;
    btn.querySelector('svg').nextSibling.textContent = ' ' + installingText;
});
</script>
@endsection
