{{--
 | Contensio - The open content platform for Laravel.
 | Admin — Activity log viewer.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('cms::admin.layout')

@section('title', 'Activity log')

@section('breadcrumb')
    <a href="{{ route('cms.admin.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">Activity log</span>
@endsection

@section('content')

<div class="mb-5">
    <h1 class="text-xl font-bold text-gray-900">Activity log</h1>
    <p class="text-sm text-gray-500 mt-0.5">Audit trail of recent admin actions — who did what and when.</p>
</div>

{{-- Filters --}}
<form method="GET" class="mb-4 bg-white rounded-xl border border-gray-200 p-4">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">User</label>
            <select name="user"
                    class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Everyone</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ (int) $user === (int) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Action</label>
            <select name="action"
                    class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Any</option>
                @foreach($actions as $a)
                <option value="{{ $a }}" {{ $action === $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Subject</label>
            <select name="subject"
                    class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Any</option>
                @foreach($subjects as $s)
                <option value="{{ $s }}" {{ $subject === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}"
                   class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
    </div>
    <div class="flex items-center gap-2 mt-3">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-1.5 rounded-lg transition-colors">
            Filter
        </button>
        @if($user || $action || $subject || $from || $to)
        <a href="{{ route('cms.admin.activity-log.index') }}"
           class="text-sm text-gray-600 hover:text-gray-900">Clear</a>
        @endif
    </div>
</form>

@if($entries->isEmpty())

<div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
    <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
        <i class="bi bi-clock-history text-gray-400 text-3xl"></i>
    </div>
    <h3 class="text-base font-semibold text-gray-900 mb-1">No activity yet</h3>
    <p class="text-sm text-gray-500 max-w-sm mx-auto">
        Admin actions will appear here as users interact with the panel.
    </p>
</div>

@else

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="text-left font-medium px-5 py-3 w-44">When</th>
                <th class="text-left font-medium px-5 py-3">User</th>
                <th class="text-left font-medium px-5 py-3 w-28">Action</th>
                <th class="text-left font-medium px-5 py-3 w-32">Subject</th>
                <th class="text-left font-medium px-5 py-3">Details</th>
                <th class="text-left font-medium px-5 py-3 w-32">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($entries as $e)
            <tr class="hover:bg-gray-50 align-top">
                <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">
                    <div class="text-gray-700">{{ $e->created_at->format('M j, Y') }}</div>
                    <div>{{ $e->created_at->format('H:i:s') }}</div>
                </td>
                <td class="px-5 py-3 text-gray-900">
                    {{ $e->user?->name ?? '—' }}
                </td>
                <td class="px-5 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                        {{ $e->action }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-700 text-xs">
                    {{ $e->subject_type }}@if($e->subject_id) #{{ $e->subject_id }}@endif
                </td>
                <td class="px-5 py-3 text-gray-700 text-xs">
                    {{ $e->description }}
                </td>
                <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $e->ip_address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $entries->links() }}
</div>

@endif

@endsection
