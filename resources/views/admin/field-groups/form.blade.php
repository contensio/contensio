{{--
 | Contensio - Custom Field Group — create/edit form.
 | When editing (group exists), the fields builder is rendered below the basic-info form.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('contensio::admin.layout')

@section('title', $group->exists ? $group->label : 'New Field Group')

@section('breadcrumb')
    <a href="{{ route('contensio.account.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('contensio.account.field-groups.index') }}" class="text-gray-500 hover:text-gray-700">Custom Fields</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">{{ $group->exists ? $group->label : 'New' }}</span>
@endsection

@section('content')

<div class="max-w-4xl mx-auto">

    @if (session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
        <ul class="text-sm text-red-700 space-y-0.5">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- ── BASIC INFO ───────────────────────────────────────────────── --}}
    <form method="POST"
          action="{{ $group->exists ? route('contensio.account.field-groups.update', $group->id) : route('contensio.account.field-groups.store') }}"
          class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        @csrf
        @if($group->exists) @method('PUT') @endif

        <div class="p-5 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                <input type="text" name="label" required
                       value="{{ old('label', $group->label) }}"
                       placeholder="e.g. Device specifications"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Shown in the admin UI.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Key</label>
                <input type="text" name="key" required
                       value="{{ old('key', $group->key) }}"
                       placeholder="e.g. device-specs"
                       pattern="[a-z0-9\-_]+"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Machine key. Lowercase letters, numbers, dashes, underscores. Used in code, not shown to end users.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="description" rows="2"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">{{ old('description', $group->description) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Helps you remember what this group is for. Not shown to content editors.</p>
            </div>

        </div>

        <div class="flex items-center justify-between gap-3 px-5 py-3 bg-gray-50 border-t border-gray-100">
            <a href="{{ route('contensio.account.field-groups.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to list</a>
            <button type="submit"
                    class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                {{ $group->exists ? 'Save changes' : 'Create group' }}
            </button>
        </div>
    </form>

    {{-- ── FIELDS BUILDER (only when group exists) ──────────────────── --}}
    @if($group->exists)
        @include('contensio::admin.field-groups.fields-builder', ['group' => $group])
    @else
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 text-sm text-blue-800">
        <i class="bi bi-info-circle mr-1"></i>
        Save the group first, then come back here to add fields.
    </div>
    @endif

</div>

@endsection
