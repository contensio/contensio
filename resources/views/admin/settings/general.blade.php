{{--
 | Contensio - The open content platform for Laravel.
 | Admin — settings general.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'General Settings')

@section('breadcrumb')
    <a href="{{ route('contensio.account.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">General</span>
@endsection

@section('content')

@if (session('success'))
<div class="mb-5 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-3">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 rounded-md px-4 py-3">
    <ul class="text-sm text-red-700 space-y-0.5">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900">General Settings</h1>
    <p class="text-sm text-gray-500 mt-0.5">Basic site identity and display preferences.</p>
</div>

<form method="POST" action="{{ route('contensio.account.settings.general.save') }}" class="max-w-lg space-y-5">
@csrf

    {{-- Site identity --}}
    <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-800">Site Identity</h2>
        </div>
        <div class="px-5 py-5 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Site name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="site_name"
                       value="{{ old('site_name', $settings['site_name'] ?? config('app.name')) }}"
                       required
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-400">Displayed in the browser tab, header, and emails.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tagline</label>
                <input type="text"
                       name="site_tagline"
                       value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}"
                       placeholder="Just another website"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-400">A short description shown on the homepage.</p>
            </div>

        </div>
    </div>

    {{-- Branding --}}
    <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-800">Branding</h2>
            <p class="text-xs text-gray-500 mt-0.5">Logo and favicon shown on the public-facing site.</p>
        </div>
        <div class="px-5 py-5 space-y-6">

            {{-- Site logo --}}
            <div x-data="{
                     imgUrl: @js($settings['site_logo'] ?? null),
                     init() {
                         window.addEventListener('cms:media-selected', (ev) => {
                             if (ev.detail.inputName !== 'logo_picker') return;
                             const url = ev.detail.items[0]?.url ?? null;
                             this.imgUrl = url;
                             this.$refs.logoInput.value = url ?? '';
                         });
                     },
                     remove() { this.imgUrl = null; this.$refs.logoInput.value = ''; },
                 }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Site logo</label>

                <input type="hidden" name="site_logo" x-ref="logoInput"
                       value="{{ old('site_logo', $settings['site_logo'] ?? '') }}">

                <div x-show="imgUrl" x-cloak
                     class="relative mb-3 h-16 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex items-center px-4">
                    <img :src="imgUrl" alt="Logo preview" class="max-h-10 w-auto max-w-[200px] object-contain">
                    <button type="button" @click="remove()"
                            class="absolute top-2 right-2 w-6 h-6 bg-white/90 hover:bg-white rounded-full
                                   flex items-center justify-center text-gray-500 hover:text-red-600 shadow-sm transition-colors"
                            title="Remove">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="! imgUrl"
                     class="mb-3 h-16 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50
                            flex items-center justify-center text-xs text-gray-400">
                    No logo — site name text is shown instead
                </div>

                <button type="button"
                        @click="$dispatch('cms:media-pick', { inputName: 'logo_picker', accept: 'image/', multiple: false })"
                        class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 border border-gray-300
                               hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors"
                        x-text="imgUrl ? 'Change logo' : 'Select from Media Library'">
                </button>
                <p class="mt-2 text-xs text-gray-400">PNG or SVG with a transparent background. Displayed at 32 px height in the site header.</p>
            </div>

            {{-- Favicon --}}
            <div x-data="{
                     imgUrl: @js($settings['site_favicon'] ?? null),
                     init() {
                         window.addEventListener('cms:media-selected', (ev) => {
                             if (ev.detail.inputName !== 'favicon_picker') return;
                             const url = ev.detail.items[0]?.url ?? null;
                             this.imgUrl = url;
                             this.$refs.faviconInput.value = url ?? '';
                         });
                     },
                     remove() { this.imgUrl = null; this.$refs.faviconInput.value = ''; },
                 }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Favicon</label>

                <input type="hidden" name="site_favicon" x-ref="faviconInput"
                       value="{{ old('site_favicon', $settings['site_favicon'] ?? '') }}">

                <div x-show="imgUrl" x-cloak
                     class="relative mb-3 w-16 h-16 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center">
                    <img :src="imgUrl" alt="Favicon preview" class="w-10 h-10 object-contain">
                    <button type="button" @click="remove()"
                            class="absolute top-1 right-1 w-5 h-5 bg-white/90 hover:bg-white rounded-full
                                   flex items-center justify-center text-gray-500 hover:text-red-600 shadow-sm transition-colors"
                            title="Remove">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="! imgUrl"
                     class="mb-3 w-16 h-16 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50
                            flex items-center justify-center text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>

                <button type="button"
                        @click="$dispatch('cms:media-pick', { inputName: 'favicon_picker', accept: 'image/', multiple: false })"
                        class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 border border-gray-300
                               hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors"
                        x-text="imgUrl ? 'Change favicon' : 'Select from Media Library'">
                </button>
                <p class="mt-2 text-xs text-gray-400">ICO, PNG, or SVG. Recommended: 32 × 32 px or 512 × 512 px PNG. Falls back to the Contensio icon if not set.</p>
            </div>

        </div>
    </div>

    {{-- Regional --}}
    <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-800">Regional</h2>
        </div>
        <div class="px-5 py-5 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Timezone <span class="text-red-500">*</span>
                </label>
                @php
                    $currentTz     = old('timezone', $settings['timezone'] ?? config('app.timezone', 'UTC'));
                    $tzIdentifiers = DateTimeZone::listIdentifiers();
                    $grouped       = [];
                    foreach ($tzIdentifiers as $tz) {
                        $parts = explode('/', $tz, 2);
                        $region = count($parts) > 1 ? $parts[0] : 'Other';
                        $grouped[$region][] = $tz;
                    }
                @endphp
                <select name="timezone"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                    @foreach($grouped as $region => $zones)
                    <optgroup label="{{ $region }}">
                        @foreach($zones as $tz)
                        <option value="{{ $tz }}" {{ $currentTz === $tz ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', $tz) }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Date format</label>
                @php
                    $currentFmt = old('date_format', $settings['date_format'] ?? 'M d, Y');
                    $formats = [
                        'M d, Y'   => 'Apr 14, 2026',
                        'd M Y'    => '14 Apr 2026',
                        'd/m/Y'    => '14/04/2026',
                        'm/d/Y'    => '04/14/2026',
                        'Y-m-d'    => '2026-04-14',
                        'd.m.Y'    => '14.04.2026',
                    ];
                @endphp
                <select name="date_format"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                    @foreach($formats as $fmt => $example)
                    <option value="{{ $fmt }}" {{ $currentFmt === $fmt ? 'selected' : '' }}>
                        {{ $example }}
                    </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-md transition-colors shadow-sm">
            Save Settings
        </button>
    </div>

</form>

@endsection
