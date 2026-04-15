{{--
 | Contensio - The open content platform for Laravel.
 | Admin — layout.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('cms::admin.dashboard.title')) — {{ config('cms.name', 'Contensio') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontSize: {
                        'sm': ['0.9375rem', { lineHeight: '1.5rem' }],
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    @stack('styles')
</head>
<body class="bg-gray-50 antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-20 bg-black/60 lg:hidden"
        @click="sidebarOpen = false">
    </div>

    {{-- ─── Sidebar ───────────────────────────────────────────────────────── --}}
    <aside
        class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 flex flex-col
               transform transition-transform duration-200 ease-in-out
               lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        {{-- Logo --}}
        <div class="flex items-center gap-2.5 px-4 h-16 border-b border-slate-700/50 shrink-0">
            <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-white font-semibold text-sm tracking-tight">{{ config('cms.name', 'Contensio') }}</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

            {{-- Dashboard --}}
            <a href="{{ route('cms.admin.dashboard') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('cms.admin.dashboard')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('cms::admin.nav.dashboard') }}
            </a>

            {{-- Content --}}
            <p class="text-xs text-slate-500 uppercase tracking-wider px-3 pt-5 pb-1.5 font-medium">{{ __('cms::admin.nav.content') }}</p>

            <a href="{{ route('cms.admin.pages.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('cms.admin.pages*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('cms::admin.nav.pages') }}
            </a>

            <a href="{{ route('cms.admin.posts.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('cms.admin.posts*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                {{ __('cms::admin.nav.posts') }}
            </a>

            @foreach($customContentTypes ?? [] as $ct)
            @php
                $ctTrans  = $ct->translations->first();
                $ctPlural = $ctTrans?->labels['plural'] ?? ucfirst($ct->name);
                $ctActive = request()->routeIs('cms.admin.content.*') && request()->route('type') === $ct->name;
            @endphp
            <a href="{{ route('cms.admin.content.index', $ct->name) }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ $ctActive ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                @if($ct->icon)
                <i class="bi {{ $ct->icon }} w-4 shrink-0 text-center text-base leading-none"></i>
                @else
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                @endif
                {{ $ctPlural }}
            </a>
            @endforeach

            {{-- Media --}}
            <p class="text-xs text-slate-500 uppercase tracking-wider px-3 pt-5 pb-1.5 font-medium">{{ __('cms::admin.nav.media') }}</p>

            <a href="{{ route('cms.admin.media.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('cms.admin.media*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ __('cms::admin.nav.media_library') }}
            </a>

            {{-- Appearance --}}
            <p class="text-xs text-slate-500 uppercase tracking-wider px-3 pt-5 pb-1.5 font-medium">{{ __('cms::admin.nav.appearance') }}</p>

            <a href="{{ route('cms.admin.themes.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('cms.admin.themes*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                Themes
            </a>

            <a href="{{ route('cms.admin.menus.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('cms.admin.menus*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                {{ __('cms::admin.nav.menus') }}
            </a>

            {{-- Users & Roles — only visible to users with users.view permission --}}
            @if(auth()->user()?->hasPermission('users.view') || auth()->user()?->hasPermission('roles.manage'))
            <p class="text-xs text-slate-500 uppercase tracking-wider px-3 pt-5 pb-1.5 font-medium">{{ __('cms::admin.nav.users') }}</p>

            @if(auth()->user()?->hasPermission('users.view'))
            <a href="{{ route('cms.admin.users.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('cms.admin.users*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                {{ __('cms::admin.nav.users') }}
            </a>
            @endif

            @if(auth()->user()?->hasPermission('roles.manage'))
            <a href="{{ route('cms.admin.roles.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('cms.admin.roles*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                Roles &amp; Permissions
            </a>
            @endif
            @endif

            {{-- Admin --}}
            <p class="text-xs text-slate-500 uppercase tracking-wider px-3 pt-5 pb-1.5 font-medium">{{ __('cms::admin.nav.admin_section') }}</p>

            <a href="{{ route('cms.admin.settings.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('cms.admin.settings*') || request()->routeIs('cms.admin.languages*') || request()->routeIs('cms.admin.content-types*') || request()->routeIs('cms.admin.taxonomies*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Configuration
            </a>

            <a href="{{ route('cms.admin.plugins.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('cms.admin.plugins*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                {{ __('cms::admin.nav.plugins') }}
            </a>

        </nav>

        {{-- Sidebar footer: current user --}}
        <div class="shrink-0 px-3 py-3 border-t border-slate-700/50">
            <div class="flex items-center gap-2.5 px-2 py-1.5">
                <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-semibold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>

    </aside>

    {{-- ─── Main content ──────────────────────────────────────────────────── --}}
    <div class="lg:pl-64 flex flex-col min-h-screen">

        {{-- Top bar --}}
        <header class="h-16 bg-white border-b border-gray-200 flex items-center gap-4 px-4 lg:px-6 sticky top-0 z-10">

            {{-- Hamburger (mobile) --}}
            <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Breadcrumb --}}
            <div class="flex-1 text-sm text-gray-600">
                @yield('breadcrumb')
            </div>

            {{-- View site --}}
            <a href="{{ url('/') }}" target="_blank"
               class="hidden sm:flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors px-3 py-1.5 rounded-lg hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                {{ __('cms::admin.header.view_site') }}
            </a>

            {{-- User menu --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-semibold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="open = false"
                     class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">
                    <a href="{{ route('cms.admin.profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('cms::admin.header.my_profile') }}
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <form method="POST" action="{{ route('cms.logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('cms::admin.header.sign_out') }}
                        </button>
                    </form>
                </div>
            </div>

        </header>

        {{-- Page content --}}
        <main class="flex-1 p-4 lg:p-6">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="px-6 py-3 border-t border-gray-100 text-xs text-gray-400 flex items-center justify-between">
            <span>{{ config('cms.name', 'Contensio') }}</span>
            <span>{{ __('cms::admin.footer.version', ['version' => '1.0.0']) }}</span>
        </footer>

    </div>

    @stack('scripts')

    {{-- ─── Icon picker modal ──────────────────────────────────────────────────── --}}
    @include('cms::admin.partials.icon-picker-modal')

    {{-- ─── Confirmation modal ───────────────────────────────────────────────── --}}
    {{--
        Trigger from any page with:
            $dispatch('cms:confirm', { title, description, confirmLabel, formId })
        The modal submits document.getElementById(formId) on confirm.
    --}}
    <div x-data="{
            isOpen: false,
            title: '',
            description: '',
            confirmLabel: 'Delete',
            formId: null,
            removeSelector: null,
            open(detail) {
                this.title          = detail.title          || 'Are you sure?';
                this.description    = detail.description    || '';
                this.confirmLabel   = detail.confirmLabel   || 'Delete';
                this.formId         = detail.formId         || null;
                this.removeSelector = detail.removeSelector || null;
                this.isOpen         = true;
            },
            confirm() {
                if (this.formId) document.getElementById(this.formId).submit();
                if (this.removeSelector) document.querySelector(this.removeSelector)?.remove();
                this.isOpen = false;
            }
         }"
         @cms:confirm.window="open($event.detail)"
         x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-0"
         style="display: none;">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="isOpen = false"></div>

        {{-- Panel --}}
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-2xl shadow-2xl w-full sm:max-w-sm mx-auto overflow-hidden">

            {{-- Top accent bar --}}
            <div class="h-1 bg-gradient-to-r from-red-400 to-red-600"></div>

            <div class="px-6 pt-7 pb-6">

                {{-- Icon --}}
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-50 ring-8 ring-red-50 mx-auto mb-5">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>

                {{-- Text --}}
                <div class="text-center mb-7">
                    <h3 class="text-base font-bold text-gray-900 mb-1.5" x-text="title"></h3>
                    <p class="text-sm text-gray-500 leading-relaxed" x-text="description"></p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3">
                    <button type="button"
                            @click="isOpen = false"
                            class="flex-1 inline-flex items-center justify-center border border-gray-300
                                   bg-white hover:bg-gray-50 text-gray-700 font-medium text-sm
                                   px-4 py-2.5 rounded-xl transition-colors focus:outline-none
                                   focus:ring-2 focus:ring-offset-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button type="button"
                            @click="confirm()"
                            class="flex-1 inline-flex items-center justify-center bg-red-600
                                   hover:bg-red-700 text-white font-medium text-sm px-4 py-2.5
                                   rounded-xl shadow-sm transition-colors focus:outline-none
                                   focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <span x-text="confirmLabel"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
