{{--
 | Contensio - The open content platform for Laravel.
 | Admin — pages index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', __('contensio::admin.pages.title'))

@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ __('contensio::admin.pages.title') }}</span>
@endsection

@section('content')

@if (session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-3">
    {{ session('error') }}
</div>
@endif
@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-3">
    {{ session('success') }}
</div>
@endif

<div x-data="{
        selected: [],
        bulkAction: 'publish',
        toggleAll() {
            const all = [{{ $items->pluck('id')->join(',') }}];
            this.selected = this.selected.length === all.length ? [] : [...all];
        },
        submitBulk() {
            if (this.bulkAction === 'delete') {
                $dispatch('cms:confirm', {
                    title: 'Delete selected pages?',
                    description: selected.length + ' pages will be permanently deleted.',
                    confirmLabel: 'Delete',
                    formId: 'bulk-form'
                });
            } else {
                document.getElementById('bulk-form').submit();
            }
        }
    }">

<form id="bulk-form" method="POST" action="{{ route('contensio.account.pages.bulk') }}" class="hidden">
    @csrf
    <input type="hidden" name="action" :value="bulkAction">
    <template x-for="id in selected" :key="id">
        <input type="hidden" name="ids[]" :value="id">
    </template>
</form>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ __('contensio::admin.pages.title') }}</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ __('contensio::admin.pages.subtitle') }}</p>
    </div>
    <a href="{{ route('contensio.account.pages.create') }}"
       class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-4 py-2 rounded-md transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('contensio::admin.pages.create') }}
    </a>
</div>

{{-- Bulk action bar --}}
<div x-show="selected.length > 0" x-cloak
     class="flex items-center gap-3 mb-3 bg-blue-50 border border-blue-200 rounded-md px-4 py-2.5">
    <span class="text-sm font-medium text-blue-800" x-text="selected.length + ' selected'"></span>
    <div class="flex items-center gap-2 ml-auto">
        <select x-model="bulkAction"
                class="text-sm border border-gray-300 rounded px-2.5 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-ember-500">
            <option value="publish">Publish</option>
            <option value="draft">Set as Draft</option>
            <option value="delete">Delete</option>
        </select>
        <button type="button" @click="submitBulk()"
                class="bg-ember-500 hover:bg-ember-600 text-white text-sm font-semibold px-3 py-1.5 rounded-md transition-colors">
            Apply
        </button>
        <button type="button" @click="selected = []"
                class="text-sm text-gray-500 hover:text-gray-700 px-2 py-1.5">
            Clear
        </button>
    </div>
</div>

@if($items->isEmpty())

<div class="bg-white border border-gray-200 rounded-md p-16 text-center">
    <div class="w-12 h-12 bg-gray-100 rounded-md flex items-center justify-center mx-auto mb-4">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
    </div>
    <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ __('contensio::admin.pages.empty_title') }}</h3>
    <p class="text-sm text-gray-400 mb-5 max-w-xs mx-auto">{{ __('contensio::admin.pages.empty_subtitle') }}</p>
    <a href="{{ route('contensio.account.pages.create') }}"
       class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-4 py-2 rounded-md transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('contensio::admin.pages.create') }}
    </a>
</div>

@else

<div class="bg-white border border-gray-200 rounded-md overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b-2 border-gray-100">
                <th class="px-4 py-2.5 w-8">
                    <input type="checkbox"
                           :checked="selected.length === {{ $items->count() }}"
                           @change="toggleAll()"
                           class="w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500">
                </th>
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Title</th>
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest hidden sm:table-cell">Author</th>
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest hidden md:table-cell">Date</th>
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Status</th>
                <th class="px-4 py-2.5 w-20"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($items as $item)
            <tr class="transition-colors group"
                :class="selected.includes({{ $item->id }}) ? 'bg-blue-50/60' : 'hover:bg-blue-50/40'">
                <td class="px-4 py-3.5">
                    <input type="checkbox"
                           :checked="selected.includes({{ $item->id }})"
                           @change="$event.target.checked ? selected.push({{ $item->id }}) : selected = selected.filter(i => i !== {{ $item->id }})"
                           class="w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500">
                </td>
                <td class="px-4 py-3.5">
                    <a href="{{ route('contensio.account.pages.edit', $item->id) }}"
                       class="font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                        {{ $item->translations->first()?->title ?? __('contensio::admin.dashboard.untitled') }}
                    </a>
                </td>
                <td class="px-4 py-3.5 text-gray-400 hidden sm:table-cell">{{ $item->author?->name ?? '—' }}</td>
                <td class="px-4 py-3.5 text-gray-400 hidden md:table-cell">{{ $item->created_at->format('M d, Y') }}</td>
                <td class="px-4 py-3.5">
                    <div class="flex flex-wrap gap-1">
                    @if($item->status === 'published')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-50 text-green-700 border border-green-200">Published</span>
                    @elseif($item->status === 'scheduled')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Scheduled</span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Draft</span>
                    @endif
                    @include('contensio::admin.reviews.partials.status-badge', ['status' => $item->review_status])
                    </div>
                </td>
                <td class="px-4 py-3.5 text-right">
                    <a href="{{ route('contensio.account.pages.edit', $item->id) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-900 border border-gray-200 hover:border-gray-300 bg-white hover:bg-gray-50 rounded px-2.5 py-1 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Edit
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endif

</div>{{-- /x-data --}}

@endsection
