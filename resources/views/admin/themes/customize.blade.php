{{--
 | Contensio - The open content platform for Laravel.
 | Admin — themes customize.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'Customize ' . ($theme['meta']['label'] ?? $name))

@section('breadcrumb')
<a href="{{ route('cms.admin.themes.index') }}" class="text-gray-400 hover:text-gray-700">Themes</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Customize</span>
@endsection

@section('content')

@php
    $firstSectionKey = $sections[0]['key'] ?? null;
@endphp

<div class="max-w-6xl mx-auto" x-data="{ activeSection: @js($firstSectionKey), previewOpen: false }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-400 uppercase tracking-wider font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4H4a1 1 0 00-1 1v7m0 0v7a1 1 0 001 1h7m-7-8l8-8"/>
                </svg>
                Active theme
            </div>
            <h1 class="text-xl font-bold text-gray-900 mt-0.5">
                Customize &mdash; {{ $theme['meta']['label'] ?? $name }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Changes apply to every page on the frontend.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" target="_blank"
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800
                      border border-gray-300 bg-white hover:bg-gray-50 px-3 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                Preview site
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800
                rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if(empty($sections))
    <div class="bg-white rounded-xl border border-gray-200 p-10 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
        </svg>
        <h3 class="font-semibold text-gray-700">No customization options</h3>
        <p class="text-sm text-gray-500 mt-1">
            This theme didn't declare any customizable settings in its <code class="text-xs bg-gray-100 px-1 rounded">theme.json</code>.
        </p>
    </div>
    @else

    <form method="POST" action="{{ route('cms.admin.themes.customize.save') }}">
        @csrf

        <div class="grid grid-cols-12 gap-6">

            {{-- ── Section sidebar ────────────────────────────────────────── --}}
            <aside class="col-span-12 md:col-span-3">
                <nav class="bg-white rounded-xl border border-gray-200 p-2 sticky top-20">
                    @foreach($sections as $section)
                    <button type="button"
                            @click="activeSection = @js($section['key'])"
                            :class="activeSection === @js($section['key'])
                                ? 'bg-blue-50 text-blue-700 font-semibold'
                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium
                                   text-left transition-colors">
                        @if(! empty($section['icon']))
                        <i class="bi {{ $section['icon'] }} text-base leading-none w-4 text-center"></i>
                        @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        @endif
                        {{ $section['label'] ?? $section['key'] }}
                    </button>
                    @endforeach
                </nav>
            </aside>

            {{-- ── Field panel ────────────────────────────────────────────── --}}
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
                                @include('cms::admin.themes.partials.field', [
                                    'field' => $field,
                                    'value' => $values[$field['key']] ?? ($field['default'] ?? null),
                                ])
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    {{-- Actions --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-xl
                                flex items-center justify-between">

                        {{-- Reset --}}
                        <form id="reset-theme-form"
                              method="POST"
                              action="{{ route('cms.admin.themes.customize.reset') }}"
                              class="hidden">
                            @csrf
                        </form>
                        <button type="button"
                                @click="$dispatch('cms:confirm', {
                                    title: 'Reset to defaults?',
                                    description: 'All your customizations will be lost.',
                                    confirmLabel: 'Reset',
                                    formId: 'reset-theme-form'
                                })"
                                class="text-sm text-gray-500 hover:text-red-600 transition-colors font-medium">
                            Reset to defaults
                        </button>

                        {{-- Save --}}
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold
                                       text-sm px-5 py-2.5 rounded-lg transition-colors">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

    @endif

</div>

@push('styles')
<style>
[x-cloak] { display: none !important; }
</style>
@endpush

@endsection
