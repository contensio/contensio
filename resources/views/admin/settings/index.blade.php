{{--
 | Contensio - The open content platform for Laravel.
 | Admin — settings index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'Configuration')

@section('breadcrumb')
    <span class="text-gray-900 font-medium">Configuration</span>
@endsection

@section('content')

<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900">Configuration</h1>
    <p class="text-sm text-gray-500 mt-0.5">Manage your site settings, languages and content structure.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

    {{-- General --}}
    <a href="{{ route('cms.admin.settings.general') }}"
       class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-blue-50 transition-colors">
                <svg class="w-5 h-5 text-slate-500 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-gray-900">General</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">Site name, tagline, timezone and date format.</p>
            </div>
        </div>
    </a>

    {{-- Plugins can register additional cards here (social login, analytics, email, etc.) --}}
    {!! \Contensio\Cms\Support\Hook::render('settings.hub_cards') !!}

    {{-- Email --}}
    <a href="{{ route('cms.admin.settings.email') }}"
       class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0 group-hover:bg-amber-100 transition-colors">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-gray-900">Email</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">SMTP settings for password reset, verification and notifications.</p>
            </div>
        </div>
    </a>

    {{-- SEO --}}
    <a href="{{ route('cms.admin.settings.seo') }}"
       class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition-colors">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-gray-900">SEO</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">Sitemap, robots.txt, default OG image, verification codes.</p>
            </div>
        </div>
    </a>

    {{-- Redirects --}}
    @if(auth()->user()?->hasPermission('seo.manage_redirects'))
    <a href="{{ route('cms.admin.redirects.index') }}"
       class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center shrink-0 group-hover:bg-rose-100 transition-colors">
                <i class="bi bi-arrow-left-right text-rose-600 text-xl"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-gray-900">Redirects</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">Forward old URLs to new ones — 301 or 302.</p>
            </div>
        </div>
    </a>
    @endif

    {{-- Languages --}}
    <a href="{{ route('cms.admin.languages.index') }}"
       class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0 group-hover:bg-blue-100 transition-colors">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Languages</h3>
                    <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">
                        {{ $stats['languages'] }} {{ Str::plural('language', $stats['languages']) }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">Add or remove site languages and set the default.</p>
            </div>
        </div>
    </a>

    {{-- Custom Fields --}}
    @if(auth()->user()?->hasPermission('fields.manage'))
    <a href="{{ route('cms.admin.field-groups.index') }}"
       class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0 group-hover:bg-indigo-100 transition-colors">
                <i class="bi bi-ui-checks-grid text-indigo-600 text-xl"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-gray-900">Custom Fields</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">Reusable field groups — attach them to any content type.</p>
            </div>
        </div>
    </a>
    @endif

    {{-- Content Types --}}
    <a href="{{ route('cms.admin.content-types.index') }}"
       class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center shrink-0 group-hover:bg-violet-100 transition-colors">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Content Types</h3>
                    <span class="text-xs font-medium text-violet-700 bg-violet-50 px-2 py-0.5 rounded-full">
                        {{ $stats['content_types'] }} {{ Str::plural('type', $stats['content_types']) }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">Define custom post types and manage their taxonomies.</p>
            </div>
        </div>
    </a>

    {{-- Activity log --}}
    @if(auth()->user()?->hasPermission('activity_log.view'))
    <a href="{{ route('cms.admin.activity-log.index') }}"
       class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 group-hover:bg-slate-200 transition-colors">
                <i class="bi bi-clock-history text-slate-600 text-xl"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-gray-900">Activity log</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">Audit trail of admin actions — read-only.</p>
            </div>
        </div>
    </a>
    @endif

    {{-- Plugins --}}
    <a href="{{ route('cms.admin.plugins.index') }}"
       class="group bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 transition-colors">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-semibold text-gray-900">Plugins</h3>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">Install, activate and configure plugins.</p>
            </div>
        </div>
    </a>

</div>

@endsection
