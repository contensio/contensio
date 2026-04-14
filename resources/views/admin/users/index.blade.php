{{--
 | Contensio - The open content platform for Laravel.
 | Admin — users list.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'Users')

@section('breadcrumb')
<span class="font-medium text-gray-700">Users</span>
@endsection

@section('content')

<div class="max-w-6xl mx-auto">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Users</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage who can access the admin panel and what they can do.</p>
        </div>
        <a href="{{ route('cms.admin.users.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
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
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $errors->first() }}
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-100">
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">User</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Email</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Roles</th>
                    <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Joined</th>
                    <th class="px-4 py-2.5 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($users as $user)
                <tr class="hover:bg-blue-50/40 transition-colors group">
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('cms.admin.users.edit', $user->id) }}" class="font-semibold text-gray-900 hover:text-blue-600 truncate">
                                    {{ $user->name }}
                                </a>
                                @if($user->id === auth()->id())
                                <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">you</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3.5">
                        @if($user->roles->isEmpty())
                        <span class="text-xs text-gray-400 italic">No role assigned</span>
                        @else
                        <div class="flex flex-wrap gap-1">
                            @foreach($user->roles as $role)
                                @php
                                    $tr  = $role->translations->firstWhere('language_id', $defaultLangId) ?? $role->translations->first();
                                    $lbl = $tr?->labels['title'] ?? $role->name;
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $lbl }}
                                </span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-xs text-gray-500">{{ $user->created_at?->format('M d, Y') }}</td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-1 justify-end opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('cms.admin.users.edit', $user->id) }}"
                               class="p-1.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                               title="Edit user">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                            @if($user->id !== auth()->id())
                            <form id="delete-user-{{ $user->id }}" method="POST"
                                  action="{{ route('cms.admin.users.destroy', $user->id) }}"
                                  class="hidden">
                                @csrf @method('DELETE')
                            </form>
                            <button type="button"
                                    @click="$dispatch('cms:confirm', {
                                        title: 'Delete user',
                                        description: 'Permanently delete &quot;{{ $user->name }}&quot;? Their content will be orphaned (still visible, author shown as deleted).',
                                        confirmLabel: 'Delete',
                                        formId: 'delete-user-{{ $user->id }}'
                                    })"
                                    class="p-1.5 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                    title="Delete user">
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

    <div class="mt-6 flex items-center justify-between text-sm">
        <a href="{{ route('cms.admin.roles.index') }}" class="text-blue-600 hover:text-blue-700 font-medium inline-flex items-center gap-1.5">
            Manage roles and permissions
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

</div>

@endsection
