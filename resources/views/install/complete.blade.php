{{--
 | Contensio - The open content platform for Laravel.
 | Install — complete.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::install.layout')
@php($currentStep = 4)

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">

    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('contensio::install.complete.title') }}</h1>
    <p class="text-gray-500 mb-8">{{ __('contensio::install.complete.subtitle') }}</p>

    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-left mb-8 space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">{{ __('contensio::install.complete.website_url') }}</span>
            <a href="{{ $website_url }}" target="_blank" class="text-blue-600 text-sm font-medium hover:underline">
                {{ $website_url }}
            </a>
        </div>
        <div class="border-t border-gray-200"></div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">{{ __('contensio::install.complete.admin_panel') }}</span>
            <a href="{{ $admin_url }}" class="text-blue-600 text-sm font-medium hover:underline">
                {{ $admin_url }}
            </a>
        </div>
    </div>

    <a href="{{ $admin_url }}"
       class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-medium px-8 py-3 rounded-lg transition-colors text-lg">
        {{ __('contensio::install.complete.go_to_admin') }}
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
    </a>

    <p class="text-gray-400 text-xs mt-8">
        {!! __('contensio::install.complete.note', ['key' => '<code class="bg-gray-100 px-1 py-0.5 rounded">CONTENSIO_INSTALLED=true</code>']) !!}
    </p>

</div>
@endsection
