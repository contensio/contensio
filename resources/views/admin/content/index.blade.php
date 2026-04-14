{{--
 | Contensio - The open content platform for Laravel.
 | Admin — content index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', $plural)

@section('breadcrumb')
    <span class="text-gray-900 font-medium">{{ $plural }}</span>
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

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ $plural }}</h1>
        @php $singular = $contentType->translations->first()?->labels['singular'] ?? ucfirst($type); @endphp
        <p class="text-sm text-gray-400 mt-0.5">Manage {{ strtolower($plural) }}.</p>
    </div>
    <a href="{{ route('cms.admin.content.create', $type) }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-4 py-2 rounded-md transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add {{ $singular }}
    </a>
</div>

@if($items->isEmpty())

<div class="bg-white border border-gray-200 rounded-md p-16 text-center">
    <div class="w-12 h-12 bg-gray-100 rounded-md flex items-center justify-center mx-auto mb-4">
        @if($contentType->icon)
        <i class="bi {{ $contentType->icon }} text-xl text-gray-400"></i>
        @else
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        @endif
    </div>
    <h3 class="text-sm font-semibold text-gray-900 mb-1">No {{ strtolower($plural) }} yet</h3>
    <p class="text-sm text-gray-400 mb-5 max-w-xs mx-auto">Create your first {{ strtolower($singular) }} to get started.</p>
    <a href="{{ route('cms.admin.content.create', $type) }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-4 py-2 rounded-md transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add {{ $singular }}
    </a>
</div>

@else

<div class="bg-white border border-gray-200 rounded-md overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b-2 border-gray-100">
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Title</th>
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest hidden sm:table-cell">Author</th>
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest hidden md:table-cell">Date</th>
                <th class="text-left px-4 py-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">Status</th>
                <th class="px-4 py-2.5 w-20"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($items as $item)
            <tr class="hover:bg-blue-50/40 transition-colors group">
                <td class="px-4 py-3.5">
                    <a href="{{ route('cms.admin.content.edit', [$type, $item->id]) }}"
                       class="font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                        {{ $item->translations->first()?->title ?? '(untitled)' }}
                    </a>
                </td>
                <td class="px-4 py-3.5 text-gray-400 hidden sm:table-cell">{{ $item->author?->name ?? '—' }}</td>
                <td class="px-4 py-3.5 text-gray-400 hidden md:table-cell">{{ $item->created_at->format('M d, Y') }}</td>
                <td class="px-4 py-3.5">
                    @if($item->status === 'published')
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                        Published
                    </span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        Draft
                    </span>
                    @endif
                </td>
                <td class="px-4 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('cms.admin.content.edit', [$type, $item->id]) }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-900 border border-gray-200 hover:border-gray-300 bg-white hover:bg-gray-50 rounded px-2.5 py-1 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            Edit
                        </a>
                        <form id="delete-{{ $item->id }}"
                              method="POST"
                              action="{{ route('cms.admin.content.destroy', [$type, $item->id]) }}"
                              class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button"
                                @click="$dispatch('cms:confirm', {
                                    title: 'Delete entry?',
                                    description: 'This will permanently delete this entry. This action cannot be undone.',
                                    confirmLabel: 'Delete',
                                    formId: 'delete-{{ $item->id }}'
                                })"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 hover:text-red-700 border border-red-200 hover:bg-red-50 rounded px-2.5 py-1 transition-colors">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endif

@endsection
