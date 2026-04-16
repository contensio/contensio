{{--
 | Contensio - The open content platform for Laravel.
 | Admin — create role.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Add Role')

@section('breadcrumb')
<a href="{{ route('contensio.account.roles.index') }}" class="text-gray-400 hover:text-gray-700">Roles</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Add</span>
@endsection

@section('content')

<div class="max-w-4xl mx-auto">

    <h1 class="text-xl font-bold text-gray-900 mb-1">Add Role</h1>
    <p class="text-sm text-gray-500 mb-5">Define a custom role with its own set of permissions.</p>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        @foreach($errors->all() as $err)
        <p>{{ $err }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('contensio.account.roles.store') }}">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="label" value="{{ old('label') }}" required maxlength="100" autofocus
                       placeholder="e.g. Shop Manager"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2" maxlength="500"
                          placeholder="Short description of what users with this role can do"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm resize-y
                                 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <h2 class="text-base font-bold text-gray-900 mb-1">Permissions</h2>
            <p class="text-xs text-gray-500 mb-4">Tick everything this role can do. For full access, use the Super admin permission.</p>

            @include('contensio::admin.roles.partials.permission-matrix', [
                'permissions' => $permissions,
                'assignedIds' => [],
            ])
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('contensio.account.roles.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 font-medium">Cancel</a>
            <button type="submit"
                    class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                Create Role
            </button>
        </div>
    </form>

</div>

@endsection
