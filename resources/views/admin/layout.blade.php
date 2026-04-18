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
    <title>@yield('title', __('contensio::admin.dashboard.title')) — {{ config('contensio.name', 'Contensio') }}</title>
    <link rel="icon" type="image/png" href="{{ asset(config('contensio.admin_favicon', 'vendor/contensio/img/favicon128x128.png')) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontSize: {
                        'sm': ['0.9375rem', { lineHeight: '1.5rem' }],
                    },
                    colors: {
                        'ember-500': '#d04a1f',
                        'ember-600': '#b23e18',
                        'ember-700': '#8f3112',
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>[x-cloak] { display: none !important; }</style>
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
        <div class="flex items-center px-4 h-16 border-b border-slate-700/50 shrink-0">
            <a href="{{ route('contensio.account.dashboard') }}" class="inline-flex items-center">
                <img src="{{ asset(config('contensio.admin_logo_dark', 'vendor/contensio/img/logo-backend.png')) }}"
                     alt="{{ config('contensio.name', 'Contensio') }}">
            </a>
        </div>

        {{-- Navigation --}}
        @php
            // Collapsible "Appearance" children — Themes + Menus are built-in;
            // plugins stack beneath via placement=appearance.
            $appearanceChildren = array_merge(
                [
                    ['label' => 'Themes', 'icon' => 'bi-palette',   'route' => 'contensio.account.themes.index'],
                    ['label' => 'Menus',  'icon' => 'bi-list-task', 'route' => 'contensio.account.menus.index'],
                ],
                \Contensio\Support\AdminNavigation::appearanceItems()
            );

            // Collapsible "Tools" children — Import/Export is built-in;
            // plugins stack beneath via placement=tools.
            $toolsChildren = array_merge(
                [
                    [
                        'label' => 'Import / Export',
                        'icon'  => 'bi-arrow-left-right',
                        'route' => 'contensio.account.tools.import-export',
                    ],
                    [
                        'label' => 'Backups',
                        'icon'  => 'bi-archive',
                        'route' => 'contensio.account.tools.backups',
                    ],
                    [
                        'label'      => 'Activity log',
                        'icon'       => 'bi-clock-history',
                        'route'      => 'contensio.account.activity-log.index',
                        'permission' => 'activity_log.view',
                    ],
                ],
                \Contensio\Support\AdminNavigation::toolsItems()
            );

            // Filter each list by permission + determine auto-open state
            $filterAndOpen = function (array $items) {
                $items = array_values(array_filter($items, function ($item) {
                    if (empty($item['permission'])) return true;
                    return auth()->user()?->hasPermission($item['permission']);
                }));
                $open = false;
                foreach ($items as $item) {
                    if (! empty($item['route']) && request()->routeIs($item['route'] . '*')) {
                        $open = true;
                        break;
                    }
                }
                return [$items, $open];
            };
            [$appearanceChildren, $appearanceOpen] = $filterAndOpen($appearanceChildren);
            [$toolsChildren,      $toolsOpen]      = $filterAndOpen($toolsChildren);

            $rootPluginItems = \Contensio\Support\AdminNavigation::rootItems();
        @endphp

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

            {{-- Dashboard --}}
            <a href="{{ route('contensio.account.dashboard') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('contensio.account.dashboard')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('contensio::admin.nav.dashboard') }}
            </a>

            <a href="{{ route('contensio.account.pages.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('contensio.account.pages*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('contensio::admin.nav.pages') }}
            </a>

            <a href="{{ route('contensio.account.posts.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('contensio.account.posts*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                {{ __('contensio::admin.nav.posts') }}
            </a>

            @foreach($customContentTypes ?? [] as $ct)
            @php
                $ctTrans  = $ct->translations->first();
                $ctPlural = $ctTrans?->labels['plural'] ?? ucfirst($ct->name);
                $ctActive = request()->routeIs('contensio.account.content.*') && request()->route('type') === $ct->name;
            @endphp
            <a href="{{ route('contensio.account.content.index', $ct->name) }}"
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

            {{-- Contact --}}
            @php $contactUnread = 0;
                try { $contactUnread = \Contensio\Models\ContactMessage::where('status', \Contensio\Models\ContactMessage::STATUS_NEW)->count(); } catch (\Throwable) {}
            @endphp
            <a href="{{ route('contensio.account.contact.messages.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('contensio.account.contact*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <i class="bi bi-envelope w-4 shrink-0 text-center text-base leading-none"></i>
                <span class="flex-1">Contact</span>
                @if($contactUnread > 0)
                <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-semibold bg-ember-500 text-white">
                    {{ $contactUnread }}
                </span>
                @endif
            </a>

            @if(auth()->user()?->hasPermission('comments.manage'))
            @php $pendingComments = \Contensio\Models\Comment::where('status', 'pending')->count(); @endphp
            <a href="{{ route('contensio.account.comments.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('contensio.account.comments*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="flex-1">Comments</span>
                @if($pendingComments > 0)
                <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-semibold bg-amber-500 text-white">
                    {{ $pendingComments }}
                </span>
                @endif
            </a>
            @endif

            <a href="{{ route('contensio.account.media.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('contensio.account.media*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ __('contensio::admin.nav.media_library') }}
            </a>

            {{-- Appearance — collapsible; Themes + Menus are built-in, plugins with placement=appearance stack beneath --}}
            <div x-data="{ open: {{ $appearanceOpen ? 'true' : 'false' }} }">
                <button type="button"
                        @click="open = !open"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                               text-slate-400 hover:text-white hover:bg-slate-800">
                    <i class="bi bi-palette w-4 shrink-0 text-center text-base leading-none"></i>
                    <span class="flex-1 text-left">{{ __('contensio::admin.nav.appearance') }}</span>
                    <svg class="w-3 h-3 shrink-0 transition-transform" :class="open ? 'rotate-90' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak class="mt-0.5 ml-4 pl-2 border-l border-slate-700/50 space-y-0.5">
                    @foreach($appearanceChildren as $item)
                        @php
                            $href = ! empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])
                                ? route($item['route'])
                                : ($item['url'] ?? '#');
                            $isActive = ! empty($item['route']) && request()->routeIs($item['route'] . '*');
                        @endphp
                        <a href="{{ $href }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                  {{ $isActive ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                            @if(! empty($item['icon']))
                            <i class="bi {{ $item['icon'] }} w-4 shrink-0 text-center text-base leading-none"></i>
                            @else
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            </svg>
                            @endif
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Tools — collapsible; Import/Export is built-in, plugins with placement=tools stack beneath --}}
            <div x-data="{ open: {{ $toolsOpen ? 'true' : 'false' }} }">
                <button type="button"
                        @click="open = !open"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                               text-slate-400 hover:text-white hover:bg-slate-800">
                    <i class="bi bi-tools w-4 shrink-0 text-center text-base leading-none"></i>
                    <span class="flex-1 text-left">Tools</span>
                    <svg class="w-3 h-3 shrink-0 transition-transform" :class="open ? 'rotate-90' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak class="mt-0.5 ml-4 pl-2 border-l border-slate-700/50 space-y-0.5">
                    @foreach($toolsChildren as $item)
                        @php
                            $href = ! empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])
                                ? route($item['route'])
                                : ($item['url'] ?? '#');
                            $isActive = ! empty($item['route']) && request()->routeIs($item['route'] . '*');
                        @endphp
                        <a href="{{ $href }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                  {{ $isActive ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                            @if(! empty($item['icon']))
                            <i class="bi {{ $item['icon'] }} w-4 shrink-0 text-center text-base leading-none"></i>
                            @else
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            </svg>
                            @endif
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Root-placed plugin links (inline — no group header) --}}
            @foreach($rootPluginItems as $item)
                @if(! empty($item['permission']) && ! auth()->user()?->hasPermission($item['permission']))
                    @continue
                @endif
                @php
                    $href = ! empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])
                        ? route($item['route'])
                        : ($item['url'] ?? '#');
                    $isActive = ! empty($item['route']) && request()->routeIs($item['route'] . '*');
                @endphp
                <a href="{{ $href }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ $isActive ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    @if(! empty($item['icon']))
                    <i class="bi {{ $item['icon'] }} w-4 shrink-0 text-center text-base leading-none"></i>
                    @else
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    @endif
                    {{ $item['label'] }}
                </a>
            @endforeach

            {{-- Users --}}
            @if(auth()->user()?->hasPermission('users.view'))
            <a href="{{ route('contensio.account.users.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('contensio.account.users*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                {{ __('contensio::admin.nav.users') }}
            </a>
            @endif

            <a href="{{ route('contensio.account.settings.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('contensio.account.settings*') || request()->routeIs('contensio.account.languages*') || request()->routeIs('contensio.account.content-types*') || request()->routeIs('contensio.account.taxonomies*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Configuration
            </a>

            <a href="{{ route('contensio.account.plugins.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('contensio.account.plugins*')
                          ? 'bg-slate-700 text-white'
                          : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                {{ __('contensio::admin.nav.plugins') }}
            </a>

        </nav>

        {{-- Sidebar footer: current user --}}
        <div class="shrink-0 px-3 py-3 border-t border-slate-700/50">
            <div class="flex items-center gap-2.5 px-2 py-1.5">
                @if(auth()->user()->avatar_path)
                <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" alt=""
                     class="w-7 h-7 rounded-full object-cover shrink-0">
                @else
                <div class="w-7 h-7 rounded-full bg-ember-500 flex items-center justify-center text-white text-xs font-semibold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                @endif
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

            {{-- Global search --}}
            <form method="GET" action="{{ route('contensio.account.search') }}"
                  class="hidden md:flex items-center">
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q"
                           placeholder="Search…"
                           value="{{ request()->routeIs('contensio.account.search') ? request('q') : '' }}"
                           class="pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-lg bg-gray-50
                                  focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent
                                  w-40 focus:w-56 transition-all duration-150">
                </div>
            </form>

            {{-- View site --}}
            <a href="{{ url('/') }}" target="_blank"
               class="hidden sm:flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors px-3 py-1.5 rounded-lg hover:bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                {{ __('contensio::admin.header.view_site') }}
            </a>

            {{-- User menu --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    @if(auth()->user()->avatar_path)
                    <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" alt=""
                         class="w-7 h-7 rounded-full object-cover">
                    @else
                    <div class="w-7 h-7 rounded-full bg-ember-500 flex items-center justify-center text-white text-xs font-semibold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    @endif
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
                    <a href="{{ route('contensio.account.profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ __('contensio::admin.header.my_profile') }}
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <form method="POST" action="{{ route('contensio.logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            {{ __('contensio::admin.header.sign_out') }}
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
        @php $footerUpdate = \Contensio\Services\VersionChecker::updateInfo(); @endphp
        <footer class="px-6 py-3 border-t border-gray-100 text-xs text-gray-400 flex items-center justify-between">
            <span>{{ config('contensio.name', 'Contensio') }}</span>
            <span class="flex items-center gap-2">
                <span>{{ __('contensio::admin.footer.version', ['version' => \Contensio\ContensioServiceProvider::version()]) }}</span>
                @if($footerUpdate)
                <a href="{{ route('contensio.account.dashboard') }}"
                   class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors"
                   title="{{ __('contensio::admin.update.title', ['version' => $footerUpdate['version']]) }}">
                    <i class="bi bi-arrow-up-circle text-xs"></i>
                    {{ $footerUpdate['version'] }} {{ __('contensio::admin.update.available') }}
                </a>
                @endif
            </span>
        </footer>

    </div>

    @stack('scripts')

    {{-- ─── Icon picker modal ──────────────────────────────────────────────────── --}}
    @include('contensio::admin.partials.icon-picker-modal')

    {{-- ─── Media Library picker modal ─────────────────────────────────────────── --}}
    @include('contensio::admin.partials.media-picker')

    {{-- ─── Rich Text Editor (Tiptap) — loaded once; opts-in per textarea via window.initRTE() ─── --}}
    @include('contensio::admin.partials.rich-text-editor')

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
