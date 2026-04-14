{{--
 | Contensio - The open content platform for Laravel.
 | Admin — users index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', __('cms::admin.users.title'))

@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('cms::admin.users.title') }}</span>
@endsection

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ __('cms::admin.users.title') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('cms::admin.users.subtitle') }}</p>
    </div>
    <a href="#"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('cms::admin.users.create') }}
    </a>
</div>

@if($users->isEmpty())

<div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
    <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
    </div>
    <h3 class="text-base font-semibold text-gray-900 mb-1">{{ __('cms::admin.users.empty_title') }}</h3>
    <p class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">{{ __('cms::admin.users.empty_subtitle') }}</p>
</div>

@else

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Email</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Roles</th>
                <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($users as $user)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-semibold shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <span class="font-medium text-gray-900">{{ $user->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-gray-500 hidden sm:table-cell">{{ $user->email }}</td>
                <td class="px-5 py-3.5 hidden md:table-cell">
                    <div class="flex flex-wrap gap-1">
                        @foreach($user->roles as $role)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                            {{ $role->name }}
                        </span>
                        @endforeach
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <a href="#" class="text-blue-600 hover:text-blue-700 text-xs font-medium">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endif

@endsection
