{{--
 | Contensio - The open content platform for Laravel.
 | Install — requirements.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::install.layout')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('cms::install.requirements.title') }}</h1>
        <p class="text-gray-500 mt-1">{{ __('cms::install.requirements.subtitle') }}</p>
    </div>

    @if(!$passes)
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 flex gap-3">
        <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.538-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="font-medium text-red-800">{{ __('cms::install.requirements.failed_title') }}</p>
            <p class="text-red-700 text-sm mt-0.5">{{ __('cms::install.requirements.failed_subtitle') }}</p>
        </div>
    </div>
    @endif

    {{-- PHP Version --}}
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('cms::install.requirements.php_version') }}</h2>
        <div class="flex items-center justify-between py-3 border-b border-gray-100">
            <span class="text-gray-700">{{ __('cms::install.requirements.php_required', ['version' => $results['php']['required']]) }}</span>
            @if($results['php']['passes'])
                <span class="flex items-center gap-1.5 text-green-600 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $results['php']['current'] }}
                </span>
            @else
                <span class="flex items-center gap-1.5 text-red-600 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ $results['php']['current'] }}
                </span>
            @endif
        </div>
        @if(!$results['php']['passes'])
            <p class="text-red-600 text-sm mt-2">{{ $results['php']['message'] }}</p>
        @endif
    </div>

    {{-- Extensions --}}
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('cms::install.requirements.extensions') }}</h2>
        @foreach($results['extensions']['required'] as $ext)
        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div>
                <span class="text-gray-700">{{ $ext['name'] }}</span>
                @if(!$ext['passes'])
                    <p class="text-red-600 text-sm mt-0.5">{{ $ext['message'] }}</p>
                @endif
            </div>
            @if($ext['passes'])
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            @else
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            @endif
        </div>
        @endforeach

        @if(count($results['extensions']['recommended']) > 0)
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3 mt-5">{{ __('cms::install.requirements.recommended') }}</h2>
        @foreach($results['extensions']['recommended'] as $ext)
        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div>
                <span class="text-gray-700">{{ $ext['name'] }}</span>
                @if(!$ext['passes'])
                    <p class="text-yellow-600 text-sm mt-0.5">{{ $ext['message'] }}</p>
                @endif
            </div>
            @if($ext['passes'])
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            @else
                <svg class="w-5 h-5 text-yellow-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/></svg>
            @endif
        </div>
        @endforeach
        @endif
    </div>

    {{-- Permissions --}}
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('cms::install.requirements.permissions') }}</h2>
        @foreach($results['permissions']['items'] as $item)
        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div>
                <code class="text-sm text-gray-700 bg-gray-100 px-1.5 py-0.5 rounded">{{ $item['path'] }}</code>
                @if(!$item['passes'])
                    <p class="text-red-600 text-sm mt-1">{{ $item['message'] }}</p>
                @endif
            </div>
            @if($item['passes'])
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            @else
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Disk --}}
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('cms::install.requirements.disk_space') }}</h2>
        <div class="flex items-center justify-between py-3">
            <span class="text-gray-700">{{ __('cms::install.requirements.disk_required') }}</span>
            @if($results['disk']['passes'])
                <span class="flex items-center gap-1.5 text-green-600 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ __('cms::install.requirements.disk_available', ['mb' => $results['disk']['free_mb']]) }}
                </span>
            @else
                <span class="flex items-center gap-1.5 text-red-600 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ $results['disk']['message'] }}
                </span>
            @endif
        </div>
    </div>

    <div class="flex justify-between items-center">
        <button onclick="location.reload()" class="text-gray-500 text-sm hover:text-gray-700">
            {{ __('cms::install.requirements.check_again') }}
        </button>
        @if($passes)
        <a href="{{ route('cms.install.database') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition-colors">
            {{ __('cms::install.buttons.continue') }}
        </a>
        @else
        <button disabled class="bg-gray-200 text-gray-400 font-medium px-6 py-2.5 rounded-lg cursor-not-allowed">
            {{ __('cms::install.requirements.fix_issues') }}
        </button>
        @endif
    </div>

</div>
@endsection
