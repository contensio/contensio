{{--
 | Contensio - The open content platform for Laravel.
 | Admin — settings general.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'General Settings')

@section('breadcrumb')
    <a href="{{ route('cms.admin.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
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

<form method="POST" action="{{ route('cms.admin.settings.general.save') }}" class="max-w-lg space-y-5">
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
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-400">Displayed in the browser tab, header, and emails.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tagline</label>
                <input type="text"
                       name="site_tagline"
                       value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}"
                       placeholder="Just another website"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-400">A short description shown on the homepage.</p>
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
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-md transition-colors shadow-sm">
            Save Settings
        </button>
    </div>

</form>

@endsection
