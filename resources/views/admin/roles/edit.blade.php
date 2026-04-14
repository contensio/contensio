{{--
 | Contensio - The open content platform for Laravel.
 | Admin — edit role.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@php
    $tr          = $role->translations->firstWhere('language_id', $defaultLangId) ?? $role->translations->first();
    $label       = $tr?->labels['title']       ?? ucfirst($role->name);
    $description = $tr?->labels['description'] ?? '';
@endphp

@section('title', 'Edit role — ' . $label)

@section('breadcrumb')
<a href="{{ route('cms.admin.roles.index') }}" class="text-gray-400 hover:text-gray-700">Roles</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">{{ $label }}</span>
@endsection

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="flex items-start justify-between mb-5">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-gray-900">{{ $label }}</h1>

                @if($role->plugin_name)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                    Plugin · {{ $role->plugin_name }}
                </span>
                @elseif($role->is_system)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                    Core role
                </span>
                @else
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                    Custom
                </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-1 font-mono">{{ $role->name }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        @foreach($errors->all() as $err)
        <p>{{ $err }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('cms.admin.roles.update', $role->id) }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="label" value="{{ old('label', $label) }}" required maxlength="100"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" maxlength="500"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm resize-y
                                 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $description) }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <h2 class="text-base font-bold text-gray-900 mb-1">Permissions</h2>
            <p class="text-xs text-gray-500 mb-4">Tick everything this role can do.</p>

            @include('cms::admin.roles.partials.permission-matrix', [
                'permissions' => $permissions,
                'assignedIds' => $assignedIds,
            ])
        </div>

        <div class="flex items-center justify-between">
            @if(! $role->is_system && ! $role->plugin_name)
            <form id="delete-role-form" method="POST" action="{{ route('cms.admin.roles.destroy', $role->id) }}" class="hidden">
                @csrf @method('DELETE')
            </form>
            <button type="button"
                    @click="$dispatch('cms:confirm', {
                        title: 'Delete role',
                        description: 'Delete this role? Users assigned to it will lose this role assignment.',
                        confirmLabel: 'Delete',
                        formId: 'delete-role-form'
                    })"
                    class="text-sm text-red-600 hover:text-red-700 font-medium">
                Delete role
            </button>
            @else
            <span class="text-xs text-gray-400 italic">
                @if($role->plugin_name)
                Uninstall the plugin to remove this role.
                @else
                Core roles cannot be deleted.
                @endif
            </span>
            @endif

            <div class="flex items-center gap-3">
                <a href="{{ route('cms.admin.roles.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                    Save Changes
                </button>
            </div>
        </div>
    </form>

</div>

@endsection
