{{--
 | Contensio - Custom Field Groups — admin index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('cms::admin.layout')

@section('title', 'Custom Fields')

@section('breadcrumb')
    <a href="{{ route('cms.admin.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">Custom Fields</span>
@endsection

@section('content')

@if (session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Custom Fields</h1>
        <p class="text-sm text-gray-500 mt-0.5">Field groups are reusable libraries of fields. Attach them to one or more content types.</p>
    </div>
    <a href="{{ route('cms.admin.field-groups.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New field group
    </a>
</div>

@if($groups->isEmpty())

<div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
    <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-4">
        <i class="bi bi-ui-checks-grid text-gray-400 text-3xl"></i>
    </div>
    <h3 class="text-base font-semibold text-gray-900 mb-1">No field groups yet</h3>
    <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
        Create a group to start adding custom fields to your content types — things like price, SKU, specifications, or whatever your content needs.
    </p>
    <a href="{{ route('cms.admin.field-groups.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
        Create your first group
    </a>
</div>

@else

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="text-left font-medium px-5 py-3">Group</th>
                <th class="text-left font-medium px-5 py-3 w-32">Key</th>
                <th class="text-right font-medium px-5 py-3 w-24">Fields</th>
                <th class="text-right font-medium px-5 py-3 w-36">Attached to</th>
                <th class="px-5 py-3 w-20"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($groups as $g)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-4">
                    <a href="{{ route('cms.admin.field-groups.edit', $g->id) }}"
                       class="font-medium text-gray-900 hover:text-blue-600">{{ $g->label }}</a>
                    @if($g->description)
                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $g->description }}</p>
                    @endif
                </td>
                <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $g->key }}</td>
                <td class="px-5 py-4 text-right">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                 {{ $g->fields_count > 0 ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $g->fields_count }}
                    </span>
                </td>
                <td class="px-5 py-4 text-right">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                 {{ $g->content_types_count > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $g->content_types_count }} {{ Str::plural('type', $g->content_types_count) }}
                    </span>
                </td>
                <td class="px-5 py-4 text-right">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('cms.admin.field-groups.edit', $g->id) }}"
                           class="text-gray-500 hover:text-blue-600" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form id="delete-fg-{{ $g->id }}" method="POST" action="{{ route('cms.admin.field-groups.destroy', $g->id) }}" class="hidden">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button"
                                @click.prevent="$dispatch('cms:confirm', {
                                    title: 'Delete field group?',
                                    description: 'Fields inside will be removed. Any stored values on content will be orphaned.',
                                    confirmLabel: 'Delete',
                                    formId: 'delete-fg-{{ $g->id }}'
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

@endif

@endsection
