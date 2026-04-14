{{--
 | Contensio - The open content platform for Laravel.
 | Admin — languages index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'Languages')

@section('breadcrumb')
    <a href="{{ route('cms.admin.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">Languages</span>
@endsection

@section('content')

@if (session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
    {{ session('error') }}
</div>
@endif
@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Languages</h1>
        <p class="text-sm text-gray-500 mt-0.5">Define which languages your site supports.</p>
    </div>
    <a href="{{ route('cms.admin.languages.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Language
    </a>
</div>

@if($languages->isEmpty())

<div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
    <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
        </svg>
    </div>
    <h3 class="text-base font-semibold text-gray-900 mb-1">No languages configured</h3>
    <p class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">Add at least one language to start managing multilingual content.</p>
    <a href="{{ route('cms.admin.languages.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Language
    </a>
</div>

@else

<div class="bg-white border border-gray-200 rounded-md overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b-2 border-gray-100">
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Language</th>
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest hidden sm:table-cell">Code</th>
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest hidden md:table-cell">Direction</th>
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Status</th>
                <th class="px-4 py-2.5 w-36"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($languages as $lang)
            <tr class="hover:bg-blue-50/40 transition-colors {{ $lang->status === 'disabled' ? 'opacity-50' : '' }}">
                <td class="px-4 py-3.5">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-900">{{ $lang->name }}</span>
                        @if($lang->is_default)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-700 uppercase tracking-wide">Default</span>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3.5 hidden sm:table-cell">
                    <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-200">{{ $lang->code }}</span>
                </td>
                <td class="px-4 py-3.5 hidden md:table-cell">
                    <span class="text-xs text-gray-400 uppercase font-semibold tracking-wide">{{ $lang->direction }}</span>
                </td>
                <td class="px-4 py-3.5">
                    @if($lang->status === 'active')
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 shrink-0"></span>Active
                    </span>
                    @elseif($lang->status === 'inactive')
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>Inactive
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0"></span>Disabled
                    </span>
                    @endif
                </td>
                <td class="px-4 py-3.5">
                    <div class="flex items-center justify-end gap-3">

                        @if(! $lang->is_default)
                        <form id="default-lang-{{ $lang->id }}" method="POST"
                              action="{{ route('cms.admin.languages.default', $lang->id) }}" class="hidden">
                            @csrf
                        </form>
                        <button type="button"
                                @click="$dispatch('cms:confirm', {
                                    title: 'Set {{ addslashes($lang->name) }} as default?',
                                    description: 'This will make {{ addslashes($lang->name) }} the primary language for all content.',
                                    confirmLabel: 'Set as Default',
                                    formId: 'default-lang-{{ $lang->id }}'
                                })"
                                class="text-xs font-semibold text-gray-400 hover:text-blue-600 transition-colors whitespace-nowrap">
                            Set default
                        </button>
                        @endif

                        <a href="{{ route('cms.admin.languages.edit', $lang->id) }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-900 border border-gray-200 hover:border-gray-300 bg-white hover:bg-gray-50 rounded px-2.5 py-1 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            Edit
                        </a>

                        @if(! $lang->is_default)
                        <form id="delete-lang-{{ $lang->id }}" method="POST"
                              action="{{ route('cms.admin.languages.destroy', $lang->id) }}" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button"
                                @click="$dispatch('cms:confirm', {
                                    title: 'Delete {{ addslashes($lang->name) }}?',
                                    description: 'This will permanently remove the {{ addslashes($lang->name) }} language. Content in this language will lose its translations.',
                                    confirmLabel: 'Delete',
                                    formId: 'delete-lang-{{ $lang->id }}'
                                })"
                                class="text-xs font-semibold text-red-400 hover:text-red-600 transition-colors">Delete</button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4 flex items-start gap-2 text-xs text-gray-400">
    <svg class="w-3.5 h-3.5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span><strong class="text-gray-500">Active</strong> = shown in admin &amp; website &nbsp;·&nbsp; <strong class="text-gray-500">Inactive</strong> = admin only (prepare content before going live) &nbsp;·&nbsp; <strong class="text-gray-500">Disabled</strong> = hidden everywhere</span>
</div>

@endif

@endsection
