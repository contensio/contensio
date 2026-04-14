{{--
 | Contensio - The open content platform for Laravel.
 | Admin — plugins index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'Plugins')

@section('breadcrumb')
<span class="font-medium text-gray-700">Plugins</span>
@endsection

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Plugins</h1>
            <p class="text-sm text-gray-500 mt-0.5">Extend Contensio with plugins. Enable or disable any number at once.</p>
        </div>

        <button type="button"
                x-data
                @click="$dispatch('cms:plugin-install-open')"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
                       text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Install Plugin
        </button>
    </div>

    {{-- Flash / errors --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $errors->first() }}
    </div>
    @endif

    @if(empty($plugins))
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <svg class="w-14 h-14 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <h3 class="font-semibold text-gray-700">No plugins installed yet</h3>
        <p class="text-sm text-gray-500 mt-1 mb-4">Install your first plugin to extend what Contensio can do.</p>
        <button type="button"
                x-data
                @click="$dispatch('cms:plugin-install-open')"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
            Install Plugin
        </button>
    </div>
    @else

    {{-- Plugin grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($plugins as $name => $plugin)
        @php
            $meta      = $plugin['meta'];
            $isEnabled = in_array($name, $enabledList, true);
            $label     = $meta['label']       ?? ucwords(str_replace(['-', '_', '/'], ' ', $name));
            $desc      = $meta['description'] ?? '';
            $author    = $meta['author']      ?? '';
            $version   = $meta['version']     ?? '';
            $removable = $meta['removable']   ?? ($plugin['source'] === 'local');
            $hasProvider = ! empty($meta['provider']);
            $hasSettings = ! empty($meta['settings']['sections']);
        @endphp

        <div class="bg-white rounded-xl border-2 {{ $isEnabled ? 'border-green-500' : 'border-gray-200' }}
                    overflow-hidden flex flex-col relative">

            {{-- Enabled badge --}}
            @if($isEnabled)
            <div class="absolute top-3 right-3 z-10">
                <span class="inline-flex items-center gap-1 bg-green-600 text-white text-xs font-bold
                             px-2.5 py-1 rounded-full shadow">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enabled
                </span>
            </div>
            @endif

            {{-- Top --}}
            <div class="h-20 bg-gradient-to-br
                        {{ $isEnabled ? 'from-green-50 to-green-100' : 'from-gray-50 to-gray-100' }}
                        flex items-center justify-center border-b border-gray-100">
                <svg class="w-8 h-8 {{ $isEnabled ? 'text-green-400' : 'text-gray-300' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>

            <div class="p-4 flex-1 flex flex-col">
                <div class="flex-1">
                    <h3 class="font-bold text-gray-900 text-base pr-20">{{ $label }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $name }}</p>
                    @if($author || $version)
                    <p class="text-xs text-gray-400 mt-1">
                        @if($author){{ $author }}@endif
                        @if($author && $version) &middot; @endif
                        @if($version)v{{ $version }}@endif
                    </p>
                    @endif
                    @if($desc)
                    <p class="text-sm text-gray-500 mt-2 leading-relaxed">{{ $desc }}</p>
                    @endif

                    @if(! $hasProvider)
                    <div class="mt-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1">
                        ⚠ This plugin has no service provider declared in <code>plugin.json</code>. It will install but can't be enabled.
                    </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="mt-4 flex items-center gap-2">
                    @if(! $isEnabled)
                        {{-- Enable --}}
                        <form method="POST" action="{{ route('cms.admin.plugins.enable') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="plugin" value="{{ $name }}">
                            <button type="submit"
                                    @disabled(! $hasProvider)
                                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-200 disabled:text-gray-400
                                           disabled:cursor-not-allowed text-white text-sm font-semibold
                                           px-4 py-2 rounded-lg transition-colors">
                                Enable
                            </button>
                        </form>
                    @else
                        @if($hasSettings)
                            {{-- Settings (only when enabled + plugin declared settings) --}}
                            <a href="{{ route('cms.admin.plugins.settings', ['plugin' => $name]) }}"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700
                                      text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
                               title="Configure this plugin">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Settings
                            </a>
                        @endif

                        {{-- Disable --}}
                        <form method="POST" action="{{ route('cms.admin.plugins.disable') }}" class="{{ $hasSettings ? '' : 'flex-1' }}">
                            @csrf
                            <input type="hidden" name="plugin" value="{{ $name }}">
                            <button type="submit"
                                    class="{{ $hasSettings ? '' : 'w-full' }} border border-gray-300 bg-white hover:bg-gray-50 text-gray-700
                                           text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                                Disable
                            </button>
                        </form>
                    @endif

                    {{-- Remove (local, disabled plugins only) --}}
                    @if($removable && ! $isEnabled)
                    <form id="remove-plugin-{{ md5($name) }}"
                          method="POST"
                          action="{{ route('cms.admin.plugins.uninstall') }}"
                          class="hidden">
                        @csrf
                        <input type="hidden" name="plugin" value="{{ $name }}">
                    </form>
                    <button type="button"
                            @click="$dispatch('cms:confirm', {
                                title: 'Remove plugin',
                                description: 'Remove &quot;{{ $label }}&quot;? This deletes its files from the server.',
                                confirmLabel: 'Remove',
                                formId: 'remove-plugin-{{ md5($name) }}'
                            })"
                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            title="Remove plugin">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @endif

</div>

{{-- Install plugin modal --}}
<div x-data="{ isOpen: false }"
     @cms:plugin-install-open.window="isOpen = true"
     @keydown.escape.window="isOpen = false"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="isOpen = false"></div>

    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

        <div class="h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
        <div class="px-6 pt-6 pb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Install Plugin</h2>
            <p class="text-sm text-gray-500 mb-5">
                Upload a <code class="bg-gray-100 px-1 rounded text-xs">.zip</code> file containing a valid
                <code class="bg-gray-100 px-1 rounded text-xs">plugin.json</code>.
            </p>

            <form method="POST"
                  action="{{ route('cms.admin.plugins.install') }}"
                  enctype="multipart/form-data"
                  x-data="{ fileName: '' }">
                @csrf

                <label class="block">
                    <div class="border-2 border-dashed border-gray-300 hover:border-blue-400 rounded-xl p-6
                                text-center cursor-pointer transition-colors"
                         @click="$refs.zipInput.click()">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <p class="text-sm text-gray-500" x-text="fileName || 'Click to choose a ZIP file'"></p>
                    </div>
                    <input type="file"
                           name="zip"
                           accept=".zip"
                           class="hidden"
                           x-ref="zipInput"
                           @change="fileName = $event.target.files[0]?.name || ''">
                </label>

                <div class="mt-5 flex gap-3">
                    <button type="button"
                            @click="isOpen = false"
                            class="flex-1 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700
                                   font-medium text-sm px-4 py-2.5 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold
                                   text-sm px-4 py-2.5 rounded-xl transition-colors">
                        Install
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>

@endsection
