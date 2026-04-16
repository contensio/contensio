{{--
 | Contensio - The open content platform for Laravel.
 | Install — database.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::install.layout')
@php($currentStep = 1)

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('contensio::install.database.title') }}</h1>
        <p class="text-gray-500 mt-1">{{ __('contensio::install.database.subtitle') }}</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-700 text-sm">{{ $errors->first() }}</p>
    </div>
    @endif

    <form id="dbForm" method="POST" action="{{ route('contensio.install.database.store') }}">
        @csrf

        <div class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('contensio::install.database.host') }}</label>
                <input type="text" name="db_host" value="{{ old('db_host', 'localhost') }}"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="localhost">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('contensio::install.database.name') }}</label>
                    <input type="text" name="db_name" value="{{ old('db_name') }}"
                        class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="contensio_cms">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('contensio::install.database.username') }}</label>
                    <input type="text" name="db_username" value="{{ old('db_username') }}"
                        class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="root">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('contensio::install.database.password') }}</label>
                <input type="password" name="db_password"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="{{ __('contensio::install.database.password_placeholder') }}">
            </div>

            {{-- Advanced toggle --}}
            <div>
                <button type="button" onclick="toggleAdvanced()"
                    class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1">
                    <svg id="advancedIcon" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    {{ __('contensio::install.database.advanced') }}
                </button>

                <div id="advancedOptions" class="hidden mt-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ __('contensio::install.database.port') }}</label>
                    <input type="number" name="db_port" value="{{ old('db_port', 3306) }}"
                        class="w-32 border border-gray-300 rounded-lg px-3.5 py-2.5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Test connection result --}}
        <div id="testResult" class="hidden mt-5 p-4 rounded-lg"></div>

        <div class="flex justify-between items-center mt-8">
            <a href="{{ route('contensio.install.requirements') }}"
               class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('contensio::install.buttons.back') }}
            </a>

            <div class="flex items-center gap-3">
                <button type="button" id="testBtn" onclick="testConnection()"
                    class="border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium px-5 py-2.5 rounded-lg transition-colors flex items-center gap-2">
                    <svg id="testSpinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ __('contensio::install.database.test') }}
                </button>

                <button type="submit" id="continueBtn"
                    class="bg-ember-500 hover:bg-ember-600 text-white font-medium px-6 py-2.5 rounded-lg transition-colors flex items-center gap-2">
                    <svg id="submitSpinner" class="hidden w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ __('contensio::install.buttons.continue') }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
const connectingText = @json(__('contensio::install.database.connecting'));

function toggleAdvanced() {
    const el = document.getElementById('advancedOptions');
    const icon = document.getElementById('advancedIcon');
    el.classList.toggle('hidden');
    icon.style.transform = el.classList.contains('hidden') ? '' : 'rotate(90deg)';
}

async function testConnection() {
    const btn = document.getElementById('testBtn');
    const spinner = document.getElementById('testSpinner');
    const result = document.getElementById('testResult');

    btn.disabled = true;
    spinner.classList.remove('hidden');

    const form = document.getElementById('dbForm');
    const data = new FormData(form);
    data.append('_token', '{{ csrf_token() }}');

    try {
        const response = await fetch('{{ route('contensio.install.database.test') }}', {
            method: 'POST',
            body: data,
        });
        const json = await response.json();

        result.classList.remove('hidden');

        if (json.success) {
            result.className = 'mt-5 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium flex items-center gap-2';
            result.innerHTML = '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' + json.message;
        } else {
            result.className = 'mt-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm';
            result.innerHTML = json.message;
        }
    } catch (e) {
        result.classList.remove('hidden');
        result.className = 'mt-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm';
        result.textContent = 'An unexpected error occurred. Please try again.';
    }

    btn.disabled = false;
    spinner.classList.add('hidden');
}

document.getElementById('dbForm').addEventListener('submit', function() {
    document.getElementById('submitSpinner').classList.remove('hidden');
    document.getElementById('continueBtn').disabled = true;
});
</script>
@endsection
