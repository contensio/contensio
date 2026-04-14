{{--
 | Contensio - The open content platform for Laravel.
 | Admin — menus index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'Menus')

@section('breadcrumb')
<span class="text-gray-400">Appearance</span>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Menus</span>
@endsection

@section('content')

<div class="max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Menus</h1>
            <p class="text-sm text-gray-500 mt-0.5">Build navigation menus and assign them to locations declared by your active theme.</p>
        </div>
        <button type="button"
                x-data
                @click="$dispatch('cms:menu-create-open')"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
                       text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Menu
        </button>
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

    @if($errors->any())
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800
                rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $errors->first() }}
    </div>
    @endif

    @if($menus->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <svg class="w-14 h-14 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <h3 class="font-semibold text-gray-700">No menus yet</h3>
        <p class="text-sm text-gray-500 mt-1 mb-4">Create your first menu to start building site navigation.</p>
        <button type="button"
                x-data
                @click="$dispatch('cms:menu-create-open')"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
                       text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
            Create Menu
        </button>
    </div>
    @else

    {{-- Menus list --}}
    <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-100">
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Name</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Items</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Locations</th>
                    <th class="px-4 py-2.5 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($menus as $menu)
                @php
                    $trans = $menu->translations->firstWhere('language_id', $defaultLangId) ?? $menu->translations->first();
                    $label = $trans?->label ?? $menu->name;
                @endphp
                <tr class="hover:bg-blue-50/40 transition-colors group">
                    <td class="px-4 py-3.5">
                        <a href="{{ route('cms.admin.menus.edit', $menu->id) }}"
                           class="font-semibold text-gray-900 hover:text-blue-600">
                            {{ $label }}
                        </a>
                    </td>
                    <td class="px-4 py-3.5 text-gray-500">
                        {{ $menu->allItems->count() }}
                    </td>
                    <td class="px-4 py-3.5">
                        @if($menu->locations->isEmpty())
                        <span class="text-xs text-gray-400 italic">Unassigned</span>
                        @else
                        <div class="flex flex-wrap gap-1">
                            @foreach($menu->locations as $loc)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                         bg-blue-50 text-blue-700 border border-blue-100">
                                {{ $loc->location }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-1 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('cms.admin.menus.edit', $menu->id) }}"
                               class="p-1.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                               title="Edit menu">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>

                            <form id="delete-menu-{{ $menu->id }}" method="POST"
                                  action="{{ route('cms.admin.menus.destroy', $menu->id) }}"
                                  class="hidden">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button"
                                    @click="$dispatch('cms:confirm', {
                                        title: 'Delete menu',
                                        description: 'Remove &quot;{{ $label }}&quot;? All items will be deleted.',
                                        confirmLabel: 'Delete',
                                        formId: 'delete-menu-{{ $menu->id }}'
                                    })"
                                    class="p-1.5 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    title="Delete menu">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @endif
</div>

{{-- Create menu modal --}}
<div x-data="{ isOpen: false }"
     @cms:menu-create-open.window="isOpen = true"
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
            <h2 class="text-lg font-bold text-gray-900 mb-1">Create Menu</h2>
            <p class="text-sm text-gray-500 mb-5">Give your menu a name. You'll add items on the next screen.</p>

            <form method="POST" action="{{ route('cms.admin.menus.store') }}">
                @csrf

                <label class="block text-sm font-medium text-gray-700 mb-1">Menu name</label>
                <input type="text"
                       name="label"
                       placeholder="Main Menu"
                       maxlength="100"
                       required
                       autofocus
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                <div class="mt-6 flex gap-3">
                    <button type="button"
                            @click="isOpen = false"
                            class="flex-1 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700
                                   font-medium text-sm px-4 py-2.5 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold
                                   text-sm px-4 py-2.5 rounded-xl transition-colors">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>

@endsection
