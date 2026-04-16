{{--
 | Contensio - The open content platform for Laravel.
 | Admin — create user.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Add User')

@section('breadcrumb')
<a href="{{ route('contensio.account.users.index') }}" class="text-gray-400 hover:text-gray-700">Users</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Add</span>
@endsection

@section('content')

<div class="max-w-3xl mx-auto">

    <h1 class="text-xl font-bold text-gray-900 mb-5">Add User</h1>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        @foreach($errors->all() as $err)
        <p>{{ $err }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('contensio.account.users.store') }}">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-400">Minimum 8 characters.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <h2 class="text-sm font-bold text-gray-900 mb-1">Roles</h2>
            <p class="text-xs text-gray-500 mb-4">Assign one or more roles. Without a role, the user cannot access the admin panel.</p>

            <div class="space-y-2">
                @foreach($roles as $role)
                @php
                    $tr    = $role->translations->firstWhere('language_id', $defaultLangId) ?? $role->translations->first();
                    $label = $tr?->labels['title']       ?? ucfirst($role->name);
                    $desc  = $tr?->labels['description'] ?? '';
                @endphp
                <label class="flex items-start gap-3 cursor-pointer group p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                           {{ in_array($role->id, (array) old('roles', [])) ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">{{ $label }}</p>
                        @if($desc)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $desc }}</p>
                        @endif
                    </div>
                    @if($role->plugin_name)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700">plugin</span>
                    @elseif($role->is_system)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">core</span>
                    @endif
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('contensio.account.users.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</a>
            <button type="submit"
                    class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                Create User
            </button>
        </div>
    </form>

</div>

@endsection
