{{--
 | Contensio - The open content platform for Laravel.
 | Admin — contact labels management.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', __('contensio::admin.contact.labels.title'))

@section('breadcrumb')
    <a href="{{ route('contensio.account.contact.index') }}" class="text-gray-500 hover:text-gray-700">
        {{ __('contensio::admin.contact.title') }}
    </a>
    <span class="mx-1.5 text-gray-400">/</span>
    <a href="{{ route('contensio.account.contact.messages.index') }}" class="text-gray-500 hover:text-gray-700">
        {{ __('contensio::admin.contact.messages.title') }}
    </a>
    <span class="mx-1.5 text-gray-400">/</span>
    <span class="text-gray-900 font-medium">{{ __('contensio::admin.contact.labels.title') }}</span>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-3">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-3">
    {{ session('error') }}
</div>
@endif

{{-- Delete forms --}}
@foreach($labels as $label)
<form id="delete-label-{{ $label->id }}" method="POST"
      action="{{ route('contensio.account.contact.labels.destroy', $label->id) }}" class="hidden">
    @csrf @method('DELETE')
</form>
@endforeach

<div class="max-w-2xl" x-data="{ showCreate: false, editId: null, newName: '', newColor: '#6366f1' }">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ __('contensio::admin.contact.labels.title') }}</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ __('contensio::admin.contact.labels.subtitle') }}</p>
        </div>
        <button type="button" @click="showCreate = !showCreate"
                class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-4 py-2 rounded-md transition-colors shadow-sm">
            <i class="bi bi-plus-lg text-sm"></i>
            {{ __('contensio::admin.contact.labels.create') }}
        </button>
    </div>

    {{-- Create form --}}
    <div x-show="showCreate" x-cloak x-transition
         class="bg-white border border-gray-200 rounded-xl p-5 mb-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">{{ __('contensio::admin.contact.labels.new_label') }}</h3>
        <form method="POST" action="{{ route('contensio.account.contact.labels.store') }}"
              class="flex items-end gap-3 flex-wrap">
            @csrf
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">
                    {{ __('contensio::admin.contact.labels.field_name') }}
                </label>
                <input type="text" name="name" x-model="newName" required
                       placeholder="{{ __('contensio::admin.contact.labels.name_placeholder') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
            </div>
            <div class="shrink-0">
                <label class="block text-xs font-medium text-gray-500 mb-1">
                    {{ __('contensio::admin.contact.labels.field_color') }}
                </label>
                <div class="flex items-center gap-2">
                    <input type="color" name="color" x-model="newColor"
                           class="w-9 h-9 p-0.5 border border-gray-300 rounded-lg cursor-pointer">
                    <span class="text-xs font-mono text-gray-500" x-text="newColor"></span>
                </div>
            </div>
            <div class="flex items-center gap-2 pb-0.5">
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold bg-ember-500 hover:bg-ember-600 text-white rounded-lg transition-colors">
                    {{ __('contensio::admin.contact.labels.save') }}
                </button>
                <button type="button" @click="showCreate = false; newName = ''; newColor = '#6366f1'"
                        class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                    {{ __('contensio::admin.cancel') }}
                </button>
            </div>
        </form>
    </div>

    {{-- Labels list --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

        @if($labels->isEmpty())
        <div class="p-12 text-center">
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="bi bi-tag text-xl text-gray-400"></i>
            </div>
            <p class="text-sm text-gray-400">{{ __('contensio::admin.contact.labels.empty') }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ __('contensio::admin.contact.labels.empty_hint') }}</p>
        </div>
        @else

        <div class="divide-y divide-gray-100">
            @foreach($labels as $label)
            <div class="px-4 py-3">

                {{-- View row --}}
                <div x-show="editId !== {{ $label->id }}"
                     class="flex items-center gap-3 min-h-[2.25rem]">
                    <span class="w-3.5 h-3.5 rounded-full shrink-0 border border-black/10"
                          style="background-color: {{ $label->color }}"></span>
                    <span class="flex-1 text-sm font-medium text-gray-800">{{ $label->name }}</span>
                    <span class="text-xs text-gray-400 tabular-nums">
                        {{ $label->messages_count }}
                        {{ $label->messages_count === 1
                            ? __('contensio::admin.contact.labels.message_singular')
                            : __('contensio::admin.contact.labels.message_plural') }}
                    </span>
                    <div class="flex items-center gap-0.5">
                        <button type="button"
                                @click="editId = {{ $label->id }}"
                                class="p-1.5 text-gray-400 hover:text-gray-700 rounded transition-colors"
                                title="{{ __('contensio::admin.contact.labels.edit') }}">
                            <i class="bi bi-pencil text-sm"></i>
                        </button>
                        <button type="button"
                                @click="$dispatch('cms:confirm', {
                                    title: '{{ __('contensio::admin.contact.labels.delete_title') }}',
                                    description: '{{ __('contensio::admin.contact.labels.delete_desc') }}',
                                    confirmLabel: '{{ __('contensio::admin.contact.labels.delete_confirm') }}',
                                    formId: 'delete-label-{{ $label->id }}'
                                })"
                                class="p-1.5 text-gray-400 hover:text-red-500 rounded transition-colors"
                                title="{{ __('contensio::admin.contact.labels.delete') }}">
                            <i class="bi bi-trash text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Edit row --}}
                <form x-show="editId === {{ $label->id }}" x-cloak
                      method="POST" action="{{ route('contensio.account.contact.labels.update', $label->id) }}"
                      class="flex items-end gap-3 flex-wrap py-0.5"
                      x-data="{ editColor: '{{ $label->color }}' }">
                    @csrf @method('PUT')
                    <div class="flex-1 min-w-[180px]">
                        <input type="text" name="name" value="{{ $label->name }}" required
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                    </div>
                    <div class="shrink-0 flex items-center gap-2">
                        <input type="color" name="color" x-model="editColor"
                               class="w-9 h-9 p-0.5 border border-gray-300 rounded-lg cursor-pointer">
                        <span class="text-xs font-mono text-gray-500" x-text="editColor"></span>
                    </div>
                    <div class="flex items-center gap-2 pb-0.5">
                        <button type="submit"
                                class="px-4 py-2 text-sm font-semibold bg-ember-500 hover:bg-ember-600 text-white rounded-lg transition-colors">
                            {{ __('contensio::admin.save') }}
                        </button>
                        <button type="button" @click="editId = null"
                                class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                            {{ __('contensio::admin.cancel') }}
                        </button>
                    </div>
                </form>

            </div>
            @endforeach
        </div>

        @endif
    </div>

</div>

@endsection
