{{--
 | Contensio - The open content platform for Laravel.
 | Admin — plugin settings.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Plugin settings — ' . ($plugin['meta']['label'] ?? $name))

@section('breadcrumb')
<a href="{{ route('contensio.account.plugins.index') }}" class="text-gray-400 hover:text-gray-700">Plugins</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Settings</span>
@endsection

@section('content')

@php
    $firstSectionKey = $sections[0]['key'] ?? null;
@endphp

<div class="max-w-6xl mx-auto" x-data="{ activeSection: @js($firstSectionKey) }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-400 uppercase tracking-wider font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Plugin settings
            </div>
            <h1 class="text-xl font-bold text-gray-900 mt-0.5">
                {{ $plugin['meta']['label'] ?? $name }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5 font-mono">{{ $name }}</p>
        </div>

        <a href="{{ route('contensio.account.plugins.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800
                  border border-gray-300 bg-white hover:bg-gray-50 px-3 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Plugins
        </a>
    </div>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('contensio.account.plugins.settings.save') }}">
        @csrf
        <input type="hidden" name="plugin" value="{{ $name }}">

        <div class="grid grid-cols-12 gap-6">

            {{-- Section sidebar --}}
            <aside class="col-span-12 md:col-span-3">
                <nav class="bg-white rounded-xl border border-gray-200 p-2 sticky top-20">
                    @foreach($sections as $section)
                    <button type="button"
                            @click="activeSection = @js($section['key'])"
                            :class="activeSection === @js($section['key'])
                                ? 'bg-blue-50 text-blue-700 font-semibold'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-left transition-colors">
                        @if(! empty($section['icon']))
                        <i class="bi {{ $section['icon'] }} text-base leading-none w-4 text-center"></i>
                        @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        @endif
                        {{ $section['label'] ?? $section['key'] }}
                    </button>
                    @endforeach
                </nav>
            </aside>

            {{-- Field panel --}}
            <div class="col-span-12 md:col-span-9">
                <div class="bg-white rounded-xl border border-gray-200">
                    @foreach($sections as $section)
                    <div x-show="activeSection === @js($section['key'])" x-cloak>
                        <div class="px-6 py-4 border-b border-gray-100">
                            <h2 class="text-base font-bold text-gray-900">{{ $section['label'] ?? $section['key'] }}</h2>
                            @if(! empty($section['description']))
                            <p class="text-sm text-gray-500 mt-0.5">{{ $section['description'] }}</p>
                            @endif
                        </div>

                        <div class="px-6 py-5 space-y-5">
                            @foreach($section['fields'] ?? [] as $field)
                                @include('contensio::admin.themes.partials.field', [
                                    'field' => $field,
                                    'value' => $values[$field['key']] ?? ($field['default'] ?? null),
                                ])
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    {{-- Actions --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-xl flex items-center justify-between">

                        <form id="reset-plugin-form" method="POST" action="{{ route('contensio.account.plugins.settings.reset') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="plugin" value="{{ $name }}">
                        </form>
                        <button type="button"
                                @click="$dispatch('cms:confirm', {
                                    title: 'Reset plugin settings?',
                                    description: 'All customizations for this plugin will be lost.',
                                    confirmLabel: 'Reset',
                                    formId: 'reset-plugin-form'
                                })"
                                class="text-sm text-gray-500 hover:text-red-600 transition-colors font-medium">
                            Reset to defaults
                        </button>

                        <button type="submit"
                                class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>

<style>[x-cloak] { display: none !important; }</style>

@endsection
