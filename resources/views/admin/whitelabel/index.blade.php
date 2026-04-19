{{--
 | Contensio - The open content platform for Laravel.
 | Admin — White-label settings.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('contensio::admin.layout')

@section('title', 'White-label')

@section('breadcrumb')
    <a href="{{ route('contensio.account.settings.index') }}" class="hover:text-gray-700 transition-colors">Configuration</a>
    <span class="mx-1.5 text-gray-400">/</span>
    <span class="text-gray-900 font-medium">White-label</span>
@endsection

@section('content')

@php
    // $licensePayload is set by the controller only when the stored key passes
    // live Ed25519 signature verification — it cannot be faked via DB.
    $isActive       = $licensePayload !== null;
    $licenseLabel   = $licensePayload['label']  ?? '';
    $licenseDomain  = $licensePayload['sub']    ?? '';
    $licenseExpires = isset($licensePayload['exp'])
        ? \Illuminate\Support\Carbon::createFromTimestamp((int) $licensePayload['exp'])->toDateString()
        : null;

    $logoUrl         = $settings['admin_logo_url']        ?? null;
    $logoDarkUrl     = $settings['admin_logo_dark_url']   ?? null;
    $faviconUrl      = $settings['admin_favicon_url']     ?? null;
    $loginBgImageUrl = $settings['login_bg_image_url']    ?? null;
    $hidePowered     = ($settings['hide_powered_by']   ?? '0') === '1';
    $hideFooter      = ($settings['hide_admin_footer'] ?? '0') === '1';
@endphp

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">White-label</h1>
        <p class="text-sm text-gray-500 mt-0.5">Replace Contensio branding in the admin panel with your own logo, favicon, and identity.</p>
    </div>
    @if($isActive)
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
        Active
    </span>
    @else
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200">
        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span>
        Inactive
    </span>
    @endif
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="mb-5 flex items-center gap-2.5 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
    <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<div class="space-y-5">

    {{-- ── License key ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">License Key</h2>
                    <p class="text-xs text-gray-500">Purchase a white-label license at
                        <a href="https://contensio.com/licenses" target="_blank" rel="noopener"
                           class="text-violet-600 hover:text-violet-700 font-medium">contensio.com/licenses</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6">

            {{-- Your domain --}}
            <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                </svg>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500">Your installation domain</p>
                    <p class="text-sm font-mono font-semibold text-gray-900">{{ $appDomain }}</p>
                </div>
                <p class="text-xs text-gray-400 ml-auto hidden sm:block">Provide this domain when purchasing</p>
            </div>

            @if($isActive)

            {{-- Active license info --}}
            <div class="mb-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                @if($licenseLabel)
                <div class="px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg">
                    <p class="text-xs text-emerald-600 font-medium mb-0.5">License holder</p>
                    <p class="text-sm font-semibold text-emerald-900">{{ $licenseLabel }}</p>
                </div>
                @endif
                <div class="px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg">
                    <p class="text-xs text-emerald-600 font-medium mb-0.5">Licensed domain</p>
                    <p class="text-sm font-semibold text-emerald-900 font-mono">{{ $licenseDomain }}</p>
                </div>
                @if($licenseExpires)
                <div class="px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg">
                    <p class="text-xs text-emerald-600 font-medium mb-0.5">Expires</p>
                    <p class="text-sm font-semibold text-emerald-900">
                        {{ \Illuminate\Support\Carbon::parse($licenseExpires)->format('M j, Y') }}
                    </p>
                </div>
                @elseif($isActive)
                <div class="px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg">
                    <p class="text-xs text-emerald-600 font-medium mb-0.5">Expires</p>
                    <p class="text-sm font-semibold text-emerald-900">Never</p>
                </div>
                @endif
            </div>

            {{-- Remove license --}}
            <form method="POST" action="{{ route('contensio.account.settings.whitelabel.license.remove') }}"
                  id="form-remove-license">
                @csrf
                <button type="button"
                        @click="$dispatch('cms:confirm', {
                            title: 'Remove license key?',
                            description: 'White-label features will be disabled immediately. Your custom logos will be preserved.',
                            confirmLabel: 'Remove',
                            formId: 'form-remove-license'
                        })"
                        class="inline-flex items-center gap-1.5 text-sm text-red-600 hover:text-red-700 font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Remove license key
                </button>
            </form>

            @else

            {{-- Enter license key form --}}
            <form method="POST" action="{{ route('contensio.account.settings.whitelabel.license') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">License key</label>
                    <textarea name="license_key"
                              rows="3"
                              placeholder="CLK1.eyJpc3MiOiJjb250ZW5zaW8uY29tIiwic..."
                              class="w-full font-mono text-sm border rounded-lg px-3 py-2.5 bg-white
                                     focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent
                                     resize-none transition-colors
                                     {{ $errors->has('license_key') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">{{ old('license_key') }}</textarea>

                    @error('license_key')
                    <p class="mt-1.5 text-sm text-red-600 flex items-start gap-1.5">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white
                               text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                    Activate license
                </button>

            </form>

            @endif

        </div>
    </div>

    {{-- ── Branding (shown always, but note when inactive it won't be applied) ── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden {{ ! $isActive ? 'opacity-60' : '' }}">

        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Branding Assets</h2>
                    <p class="text-xs text-gray-500">
                        @if(! $isActive)
                        Activate a license key above to apply custom branding.
                        @else
                        Upload your logos and favicon. Applied immediately after saving.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <form method="POST"
              action="{{ route('contensio.account.settings.whitelabel.branding') }}"
              enctype="multipart/form-data"
              {{ ! $isActive ? 'inert' : '' }}>
            @csrf

            <div class="divide-y divide-gray-100">

                {{-- Admin logo (auth pages) --}}
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Auth page logo</p>
                        <p class="text-xs text-gray-500 mt-0.5">Displayed on the login and register pages. PNG or SVG, transparent background recommended.</p>
                    </div>
                    <div class="flex-1 flex items-center gap-4">
                        <div class="w-24 h-12 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                            @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Auth logo" class="max-w-full max-h-full object-contain p-1">
                            @else
                            <span class="text-xs text-gray-400">Default</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <input type="file" name="admin_logo" accept="image/*"
                                   class="block w-full text-sm text-gray-500
                                          file:mr-3 file:py-1.5 file:px-3
                                          file:rounded-lg file:border file:border-gray-300
                                          file:text-sm file:font-medium file:bg-white file:text-gray-700
                                          hover:file:bg-gray-50 transition-colors">
                            @if($logoUrl)
                            <form method="POST" action="{{ route('contensio.account.settings.whitelabel.branding.reset') }}" class="mt-1.5 inline">
                                @csrf
                                <input type="hidden" name="field" value="admin_logo_url">
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">
                                    Remove custom logo
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Admin logo dark (sidebar) --}}
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Sidebar logo</p>
                        <p class="text-xs text-gray-500 mt-0.5">Displayed in the dark admin sidebar. Use a white or light-coloured logo.</p>
                    </div>
                    <div class="flex-1 flex items-center gap-4">
                        <div class="w-24 h-12 rounded-lg border border-gray-200 bg-slate-800 flex items-center justify-center overflow-hidden shrink-0">
                            @if($logoDarkUrl)
                            <img src="{{ $logoDarkUrl }}" alt="Sidebar logo" class="max-w-full max-h-full object-contain p-1">
                            @else
                            <span class="text-xs text-gray-500">Default</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <input type="file" name="admin_logo_dark" accept="image/*"
                                   class="block w-full text-sm text-gray-500
                                          file:mr-3 file:py-1.5 file:px-3
                                          file:rounded-lg file:border file:border-gray-300
                                          file:text-sm file:font-medium file:bg-white file:text-gray-700
                                          hover:file:bg-gray-50 transition-colors">
                            @if($logoDarkUrl)
                            <form method="POST" action="{{ route('contensio.account.settings.whitelabel.branding.reset') }}" class="mt-1.5 inline">
                                @csrf
                                <input type="hidden" name="field" value="admin_logo_dark_url">
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">
                                    Remove custom logo
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Admin favicon --}}
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Admin favicon</p>
                        <p class="text-xs text-gray-500 mt-0.5">Shown in browser tabs when in the admin panel. PNG, 128×128 or larger recommended.</p>
                    </div>
                    <div class="flex-1 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                            @if($faviconUrl)
                            <img src="{{ $faviconUrl }}" alt="Favicon" class="max-w-full max-h-full object-contain p-1">
                            @else
                            <span class="text-xs text-gray-400">Default</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <input type="file" name="admin_favicon" accept=".png,.ico,.svg,.gif"
                                   class="block w-full text-sm text-gray-500
                                          file:mr-3 file:py-1.5 file:px-3
                                          file:rounded-lg file:border file:border-gray-300
                                          file:text-sm file:font-medium file:bg-white file:text-gray-700
                                          hover:file:bg-gray-50 transition-colors">
                            @if($faviconUrl)
                            <form method="POST" action="{{ route('contensio.account.settings.whitelabel.branding.reset') }}" class="mt-1.5 inline">
                                @csrf
                                <input type="hidden" name="field" value="admin_favicon_url">
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">
                                    Remove custom favicon
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Hide "Powered by Contensio" --}}
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Powered by notice</p>
                        <p class="text-xs text-gray-500 mt-0.5">The small attribution line at the bottom of the login and register pages.</p>
                    </div>
                    <div class="flex-1">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="hidden" name="hide_powered_by" value="0">
                            <input type="checkbox" name="hide_powered_by" value="1"
                                   {{ $hidePowered ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500">
                            <span class="text-sm text-gray-700">Hide "Powered by Contensio" on auth pages</span>
                        </label>
                    </div>
                </div>

            </div>

            {{-- Save --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white
                               text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                    Save branding
                </button>
            </div>

        </form>
    </div>

    {{-- ── Identity & Footer ────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden {{ ! $isActive ? 'opacity-60' : '' }}">

        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Identity &amp; Footer</h2>
                    <p class="text-xs text-gray-500">Replace "Contensio" with your brand name in browser titles and the admin footer.</p>
                </div>
            </div>
        </div>

        <form method="POST"
              action="{{ route('contensio.account.settings.whitelabel.branding') }}"
              {{ ! $isActive ? 'inert' : '' }}>
            @csrf

            <div class="divide-y divide-gray-100">

                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Admin name</p>
                        <p class="text-xs text-gray-500 mt-0.5">Replaces "Contensio" in browser tab titles and the admin footer.</p>
                    </div>
                    <div class="flex-1">
                        <input type="text" name="admin_name"
                               value="{{ $settings['admin_name'] ?? '' }}"
                               placeholder="Contensio"
                               maxlength="100"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5
                                      focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent transition-colors">
                    </div>
                </div>

                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Admin footer</p>
                        <p class="text-xs text-gray-500 mt-0.5">Hide the footer bar at the bottom of every admin page.</p>
                    </div>
                    <div class="flex-1">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="hidden" name="hide_admin_footer" value="0">
                            <input type="checkbox" name="hide_admin_footer" value="1"
                                   {{ $hideFooter ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500">
                            <span class="text-sm text-gray-700">Hide the admin footer (name &amp; version)</span>
                        </label>
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white
                               text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                    Save
                </button>
            </div>

        </form>
    </div>

    {{-- ── Email Branding ───────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden {{ ! $isActive ? 'opacity-60' : '' }}">

        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-sky-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Email Branding</h2>
                    <p class="text-xs text-gray-500">Custom sender name and footer text in transactional emails.</p>
                </div>
            </div>
        </div>

        <form method="POST"
              action="{{ route('contensio.account.settings.whitelabel.branding') }}"
              {{ ! $isActive ? 'inert' : '' }}>
            @csrf

            <div class="divide-y divide-gray-100">

                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Sender name</p>
                        <p class="text-xs text-gray-500 mt-0.5">Shown in the email footer as the sender. Set <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">MAIL_FROM_NAME</code> in <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">.env</code> to also change the From header.</p>
                    </div>
                    <div class="flex-1">
                        <input type="text" name="email_sender_name"
                               value="{{ $settings['email_sender_name'] ?? '' }}"
                               placeholder="{{ config('app.name', 'Contensio') }}"
                               maxlength="100"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5
                                      focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent transition-colors">
                    </div>
                </div>

                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Email footer text</p>
                        <p class="text-xs text-gray-500 mt-0.5">Replaces the default "Sent by Contensio…" line. Supports line breaks.</p>
                    </div>
                    <div class="flex-1">
                        <textarea name="email_footer_text"
                                  rows="3"
                                  maxlength="500"
                                  placeholder="Sent by Acme Inc. · acme.com"
                                  class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5
                                         focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent
                                         resize-none transition-colors">{{ $settings['email_footer_text'] ?? '' }}</textarea>
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white
                               text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                    Save
                </button>
            </div>

        </form>
    </div>

    {{-- ── Color Scheme ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden {{ ! $isActive ? 'opacity-60' : '' }}">

        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-pink-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Color Scheme</h2>
                    <p class="text-xs text-gray-500">Accent color for buttons and links; sidebar background color.</p>
                </div>
            </div>
        </div>

        <form method="POST"
              action="{{ route('contensio.account.settings.whitelabel.branding') }}"
              {{ ! $isActive ? 'inert' : '' }}
              x-data>
            @csrf

            <div class="divide-y divide-gray-100">

                {{-- Accent color --}}
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Accent color</p>
                        <p class="text-xs text-gray-500 mt-0.5">Buttons, focus rings, active states.</p>
                    </div>
                    <div class="flex-1 flex items-center gap-3" x-data="{ hex: '{{ $settings['accent_color'] ?? '#d04a1f' }}' }">
                        <input type="color"
                               x-model="hex"
                               class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5 shrink-0">
                        <input type="text" name="accent_color"
                               x-model="hex"
                               placeholder="#d04a1f"
                               maxlength="7"
                               pattern="^#[0-9a-fA-F]{6}$"
                               class="w-28 text-sm font-mono border border-gray-300 rounded-lg px-3 py-2
                                      focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent transition-colors">
                        <span class="text-xs text-gray-400 hidden sm:block">Default: <code class="bg-gray-100 px-1 py-0.5 rounded">#d04a1f</code></span>
                    </div>
                </div>

                {{-- Accent dark (hover) --}}
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Accent hover color</p>
                        <p class="text-xs text-gray-500 mt-0.5">Hover state for buttons — should be a darker shade of the accent.</p>
                    </div>
                    <div class="flex-1 flex items-center gap-3" x-data="{ hex: '{{ $settings['accent_dark_color'] ?? '#b23e18' }}' }">
                        <input type="color"
                               x-model="hex"
                               class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5 shrink-0">
                        <input type="text" name="accent_dark_color"
                               x-model="hex"
                               placeholder="#b23e18"
                               maxlength="7"
                               pattern="^#[0-9a-fA-F]{6}$"
                               class="w-28 text-sm font-mono border border-gray-300 rounded-lg px-3 py-2
                                      focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent transition-colors">
                        <span class="text-xs text-gray-400 hidden sm:block">Default: <code class="bg-gray-100 px-1 py-0.5 rounded">#b23e18</code></span>
                    </div>
                </div>

                {{-- Sidebar color --}}
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Sidebar background</p>
                        <p class="text-xs text-gray-500 mt-0.5">Background color of the left navigation sidebar.</p>
                    </div>
                    <div class="flex-1 flex items-center gap-3" x-data="{ hex: '{{ $settings['sidebar_bg_color'] ?? '#0f172a' }}' }">
                        <input type="color"
                               x-model="hex"
                               class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5 shrink-0">
                        <input type="text" name="sidebar_bg_color"
                               x-model="hex"
                               placeholder="#0f172a"
                               maxlength="7"
                               pattern="^#[0-9a-fA-F]{6}$"
                               class="w-28 text-sm font-mono border border-gray-300 rounded-lg px-3 py-2
                                      focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent transition-colors">
                        <span class="text-xs text-gray-400 hidden sm:block">Default: <code class="bg-gray-100 px-1 py-0.5 rounded">#0f172a</code></span>
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white
                               text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                    Save colors
                </button>
            </div>

        </form>
    </div>

    {{-- ── Login Page ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden {{ ! $isActive ? 'opacity-60' : '' }}">

        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Login Page</h2>
                    <p class="text-xs text-gray-500">Customize the auth page background and tagline.</p>
                </div>
            </div>
        </div>

        <form method="POST"
              action="{{ route('contensio.account.settings.whitelabel.branding') }}"
              enctype="multipart/form-data"
              {{ ! $isActive ? 'inert' : '' }}
              x-data>
            @csrf

            <div class="divide-y divide-gray-100">

                {{-- Tagline --}}
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Tagline</p>
                        <p class="text-xs text-gray-500 mt-0.5">Short line displayed below the logo on login/register pages.</p>
                    </div>
                    <div class="flex-1">
                        <input type="text" name="login_tagline"
                               value="{{ $settings['login_tagline'] ?? '' }}"
                               placeholder="The easiest way to manage your content."
                               maxlength="200"
                               class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5
                                      focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent transition-colors">
                    </div>
                </div>

                {{-- Background color --}}
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Background color</p>
                        <p class="text-xs text-gray-500 mt-0.5">Page background on login/register. Hidden when a background image is set.</p>
                    </div>
                    <div class="flex-1 flex items-center gap-3" x-data="{ hex: '{{ $settings['login_bg_color'] ?? '#fbf8f0' }}' }">
                        <input type="color"
                               x-model="hex"
                               class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer p-0.5 shrink-0">
                        <input type="text" name="login_bg_color"
                               x-model="hex"
                               placeholder="#fbf8f0"
                               maxlength="7"
                               pattern="^#[0-9a-fA-F]{6}$"
                               class="w-28 text-sm font-mono border border-gray-300 rounded-lg px-3 py-2
                                      focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent transition-colors">
                        <span class="text-xs text-gray-400 hidden sm:block">Default: <code class="bg-gray-100 px-1 py-0.5 rounded">#fbf8f0</code></span>
                    </div>
                </div>

                {{-- Background image --}}
                <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="sm:w-56 shrink-0">
                        <p class="text-sm font-medium text-gray-900">Background image</p>
                        <p class="text-xs text-gray-500 mt-0.5">Full-page background on auth pages. Covers the background color when set. Max 4 MB.</p>
                    </div>
                    <div class="flex-1 flex items-center gap-4">
                        <div class="w-24 h-16 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                            @if($loginBgImageUrl)
                            <img src="{{ $loginBgImageUrl }}" alt="Login background" class="w-full h-full object-cover">
                            @else
                            <span class="text-xs text-gray-400">None</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <input type="file" name="login_bg_image" accept="image/*"
                                   class="block w-full text-sm text-gray-500
                                          file:mr-3 file:py-1.5 file:px-3
                                          file:rounded-lg file:border file:border-gray-300
                                          file:text-sm file:font-medium file:bg-white file:text-gray-700
                                          hover:file:bg-gray-50 transition-colors">
                            @if($loginBgImageUrl)
                            <form method="POST" action="{{ route('contensio.account.settings.whitelabel.branding.reset') }}" class="mt-1.5 inline">
                                @csrf
                                <input type="hidden" name="field" value="login_bg_image_url">
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">
                                    Remove background image
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white
                               text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors">
                    Save
                </button>
            </div>

        </form>
    </div>

</div>

@endsection
