{{--
 | Contensio - The open content platform for Laravel.
 | Admin — themes index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Themes')

@section('breadcrumb')
<span class="text-gray-400">Appearance</span>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Themes</span>
@endsection

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Themes</h1>
            <p class="text-sm text-gray-500 mt-0.5">Install and activate themes for your site's frontend.</p>
        </div>

        {{-- Install from ZIP --}}
        <button type="button"
                @click="$refs.installModal.classList.remove('hidden')"
                x-data
                class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white
                       text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Install Theme
        </button>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800
                rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800
                rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $errors->first() }}
    </div>
    @endif

    {{-- Theme grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($themes as $name => $theme)
        @php
            $meta      = $theme['meta'];
            $isActive  = $name === $activeName;
            $label     = $meta['label']       ?? ucwords(str_replace(['-', '_', '/'], ' ', $name));
            $desc      = $meta['description'] ?? '';
            $author    = $meta['author']      ?? '';
            $version   = $meta['version']     ?? '';
            $removable = $meta['removable']   ?? ($theme['source'] === 'local');
        @endphp

        <div class="bg-white rounded-xl border-2 {{ $isActive ? 'border-blue-500' : 'border-gray-200' }}
                    overflow-hidden flex flex-col relative">

            {{-- Active badge --}}
            @if($isActive)
            <div class="absolute top-3 right-3 z-10">
                <span class="inline-flex items-center gap-1 bg-blue-600 text-white text-xs font-bold
                             px-2.5 py-1 rounded-full shadow">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    Active
                </span>
            </div>
            @endif

            {{-- Thumbnail — screenshot served via /admin/themes/screenshot?theme=vendor/name,
                 falls back to an icon if the theme doesn't ship one. --}}
            @php
                $hasScreenshot = false;
                if (! empty($theme['path'])) {
                    foreach (['screenshot.svg', 'screenshot.png', 'screenshot.jpg', 'screenshot.webp'] as $f) {
                        if (is_file(rtrim($theme['path'], '/\\') . '/public/' . $f)) {
                            $hasScreenshot = true;
                            break;
                        }
                    }
                }
            @endphp
            <div class="h-36 bg-gradient-to-br
                        {{ $isActive ? 'from-blue-50 to-blue-100' : 'from-gray-50 to-gray-100' }}
                        flex items-center justify-center border-b border-gray-100 overflow-hidden">
                @if($hasScreenshot)
                <img src="{{ route('contensio.account.themes.screenshot', ['theme' => $name]) }}"
                     alt="{{ $label }}"
                     class="w-full h-full object-cover">
                @else
                <svg class="w-10 h-10 {{ $isActive ? 'text-blue-300' : 'text-gray-300' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                @endif
            </div>

            {{-- Info --}}
            <div class="p-4 flex-1 flex flex-col">
                <div class="flex-1">
                    <h3 class="font-bold text-gray-900 text-base">{{ $label }}</h3>
                    @if($author || $version)
                    <p class="text-xs text-gray-400 mt-0.5">
                        @if($author){{ $author }}@endif
                        @if($author && $version) &middot; @endif
                        @if($version)v{{ $version }}@endif
                    </p>
                    @endif
                    @if($desc)
                    <p class="text-sm text-gray-500 mt-2 leading-relaxed">{{ $desc }}</p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="mt-4 flex items-center gap-2">
                    @if(! $isActive)

                    {{-- Activate --}}
                    <form method="POST" action="{{ route('contensio.account.themes.activate') }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="theme" value="{{ $name }}">
                        <button type="submit"
                                class="w-full bg-ember-500 hover:bg-ember-600 text-white text-sm font-semibold
                                       px-4 py-2 rounded-lg transition-colors">
                            Activate
                        </button>
                    </form>

                    @else

                    {{-- Customize (active theme only) --}}
                    <a href="{{ route('contensio.account.themes.customize') }}"
                       class="flex-1 inline-flex items-center justify-center gap-1.5
                              bg-ember-500 hover:bg-ember-600 text-white text-sm font-semibold
                              px-4 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Customize
                    </a>

                    @endif

                    {{-- Remove (local only) --}}
                    @if($removable && ! $isActive)
                    <form id="remove-theme-{{ md5($name) }}"
                          method="POST"
                          action="{{ route('contensio.account.themes.uninstall') }}"
                          class="hidden">
                        @csrf
                        <input type="hidden" name="theme" value="{{ $name }}">
                    </form>
                    <button type="button"
                            @click="$dispatch('cms:confirm', {
                                title: 'Remove theme',
                                description: 'Remove &quot;{{ $label }}&quot;? This deletes its files from the server.',
                                confirmLabel: 'Remove',
                                formId: 'remove-theme-{{ md5($name) }}'
                            })"
                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            title="Remove theme">
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

</div>

{{-- Install from ZIP modal --}}
<div x-data x-ref="installModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
         @click="$refs.installModal.classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="h-1 bg-gradient-to-r from-blue-400 to-blue-600"></div>
        <div class="px-6 pt-6 pb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Install Theme</h2>
            <p class="text-sm text-gray-500 mb-5">
                Upload a <code class="bg-gray-100 px-1 rounded text-xs">.zip</code> file containing a valid
                <code class="bg-gray-100 px-1 rounded text-xs">theme.json</code>.
            </p>

            <form method="POST"
                  action="{{ route('contensio.account.themes.install') }}"
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
                            @click="$refs.installModal.classList.add('hidden')"
                            class="flex-1 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700
                                   font-medium text-sm px-4 py-2.5 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 bg-ember-500 hover:bg-ember-600 text-white font-semibold
                                   text-sm px-4 py-2.5 rounded-xl transition-colors">
                        Install
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
