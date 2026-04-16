{{--
 | Contensio - The open content platform for Laravel.
 | Admin — redirects index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('contensio::admin.layout')

@section('title', 'Redirects')

@section('breadcrumb')
    <a href="{{ route('contensio.account.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">Redirects</span>
@endsection

@section('content')

@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Redirects</h1>
        <p class="text-sm text-gray-500 mt-0.5">Forward old URLs to new ones — useful after moving or renaming content.</p>
    </div>
    <a href="{{ route('contensio.account.redirects.create') }}"
       class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add redirect
    </a>
</div>

<form method="GET" class="mb-4">
    <div class="relative max-w-sm">
        <input type="search" name="q" value="{{ $q }}"
               placeholder="Search by source or target URL..."
               class="w-full rounded-lg border border-gray-300 pl-9 pr-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
        <i class="bi bi-search absolute left-3 top-2.5 text-gray-400"></i>
    </div>
</form>

@if($redirects->isEmpty())

<div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
    <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
        <i class="bi bi-arrow-left-right text-gray-400 text-3xl"></i>
    </div>
    <h3 class="text-base font-semibold text-gray-900 mb-1">
        {{ $q ? 'No redirects match your search' : 'No redirects yet' }}
    </h3>
    <p class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">
        {{ $q ? 'Try a different search term.' : 'Add your first redirect to forward an old URL somewhere useful.' }}
    </p>
    @unless($q)
    <a href="{{ route('contensio.account.redirects.create') }}"
       class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
        Add redirect
    </a>
    @endunless
</div>

@else

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="text-left font-medium px-5 py-3">From</th>
                <th class="text-left font-medium px-5 py-3">To</th>
                <th class="text-left font-medium px-5 py-3 w-20">Code</th>
                <th class="text-right font-medium px-5 py-3 w-20">Hits</th>
                <th class="text-right font-medium px-5 py-3 w-28">Last hit</th>
                <th class="px-5 py-3 w-20"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($redirects as $r)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono text-xs text-gray-900 break-all">{{ $r->source_url }}</td>
                <td class="px-5 py-3 font-mono text-xs text-gray-700 break-all">{{ $r->target_url }}</td>
                <td class="px-5 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                 {{ $r->status_code == 301 ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $r->status_code }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right text-gray-600">{{ number_format($r->hits ?? 0) }}</td>
                <td class="px-5 py-3 text-right text-xs text-gray-500">
                    {{ $r->last_hit_at ? $r->last_hit_at->diffForHumans() : '—' }}
                </td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('contensio.account.redirects.edit', $r->id) }}"
                           class="text-gray-500 hover:text-blue-600" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form id="delete-redirect-{{ $r->id }}" method="POST" action="{{ route('contensio.account.redirects.destroy', $r->id) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                        <button type="button"
                                @click.prevent="$dispatch('cms:confirm', {
                                    title: 'Delete redirect?',
                                    description: 'This redirect will stop working immediately.',
                                    confirmLabel: 'Delete',
                                    formId: 'delete-redirect-{{ $r->id }}'
                                })"
                                class="text-gray-500 hover:text-red-600" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $redirects->links() }}
</div>

@endif

@endsection
