{{--
 | Contensio - The open content platform for Laravel.
 | Admin — roles list.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'Roles')

@section('breadcrumb')
<a href="{{ route('cms.admin.users.index') }}" class="text-gray-400 hover:text-gray-700">Users</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Roles</span>
@endsection

@section('content')

<div class="max-w-6xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Roles & Permissions</h1>
            <p class="text-sm text-gray-500 mt-0.5">Control what each type of user can do. Use core roles as-is, edit their permissions, or create custom roles.</p>
        </div>
        <a href="{{ route('cms.admin.roles.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Role
        </a>
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
        {{ $errors->first() }}
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-100">
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Role</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Source</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Permissions</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Users</th>
                    <th class="px-4 py-2.5 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($roles as $role)
                @php
                    $tr    = $role->translations->firstWhere('language_id', $defaultLangId) ?? $role->translations->first();
                    $label = $tr?->labels['title']       ?? ucfirst($role->name);
                    $desc  = $tr?->labels['description'] ?? '';
                    $permsCount = $role->permissions->count();
                    $hasStar    = $role->permissions->contains('name', '*');
                @endphp
                <tr class="hover:bg-blue-50/40 transition-colors group">
                    <td class="px-4 py-3.5">
                        <a href="{{ route('cms.admin.roles.edit', $role->id) }}" class="font-semibold text-gray-900 hover:text-blue-600">
                            {{ $label }}
                        </a>
                        @if($desc)
                        <p class="text-xs text-gray-500 mt-0.5 max-w-md">{{ $desc }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        @if($role->plugin_name)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                            Plugin · {{ $role->plugin_name }}
                        </span>
                        @elseif($role->is_system)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                            Core
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                            Custom
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-sm">
                        @if($hasStar)
                        <span class="inline-flex items-center gap-1 text-yellow-700">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="font-semibold">Full access</span>
                        </span>
                        @else
                        <span class="text-gray-600">{{ $permsCount }} permission{{ $permsCount === 1 ? '' : 's' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-gray-600">
                        {{ $role->users_count }}
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-1 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('cms.admin.roles.edit', $role->id) }}"
                               class="p-1.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                               title="Edit role">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                            @if(! $role->is_system && ! $role->plugin_name)
                            <form id="delete-role-{{ $role->id }}" method="POST"
                                  action="{{ route('cms.admin.roles.destroy', $role->id) }}"
                                  class="hidden">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button"
                                    @click="$dispatch('cms:confirm', {
                                        title: 'Delete role',
                                        description: 'Remove &quot;{{ $label }}&quot;? Users assigned to this role will lose it.',
                                        confirmLabel: 'Delete',
                                        formId: 'delete-role-{{ $role->id }}'
                                    })"
                                    class="p-1.5 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    title="Delete role">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection
