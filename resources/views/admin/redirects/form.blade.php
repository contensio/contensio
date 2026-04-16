{{--
 | Contensio - The open content platform for Laravel.
 | Admin — redirect create/edit form.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('cms::admin.layout')

@section('title', $redirect->exists ? 'Edit Redirect' : 'New Redirect')

@section('breadcrumb')
    <a href="{{ route('cms.admin.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('cms.admin.redirects.index') }}" class="text-gray-500 hover:text-gray-700">Redirects</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">{{ $redirect->exists ? 'Edit' : 'New' }}</span>
@endsection

@section('content')

<div class="max-w-2xl mx-auto">

    <h1 class="text-xl font-bold text-gray-900 mb-5">{{ $redirect->exists ? 'Edit redirect' : 'New redirect' }}</h1>

    @if ($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
        <ul class="text-sm text-red-700 space-y-0.5">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $redirect->exists ? route('cms.admin.redirects.update', $redirect->id) : route('cms.admin.redirects.store') }}"
          class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @csrf
        @if($redirect->exists) @method('PUT') @endif

        <div class="p-5 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From (source URL)</label>
                <input type="text" name="source_url" required
                       value="{{ old('source_url', $redirect->source_url) }}"
                       placeholder="/old-page"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Start with <code class="bg-gray-100 px-1 rounded">/</code> — e.g. <code class="bg-gray-100 px-1 rounded">/old-slug</code>.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To (target URL)</label>
                <input type="text" name="target_url" required
                       value="{{ old('target_url', $redirect->target_url) }}"
                       placeholder="/new-page or https://example.com"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Relative (<code class="bg-gray-100 px-1 rounded">/new-page</code>) or absolute URL.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <div class="space-y-2">
                    @foreach([301 => 'Permanent (301) — preferred for renamed content', 302 => 'Temporary (302) — useful during migrations or A/B tests'] as $code => $label)
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="status_code" value="{{ $code }}"
                               {{ (int) old('status_code', $redirect->status_code) === $code ? 'checked' : '' }}
                               class="mt-0.5 w-4 h-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700"><strong>{{ $code }}</strong> — {{ \Illuminate\Support\Str::after($label, '—') }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            @if($redirect->exists && $redirect->hits)
            <div class="pt-3 border-t border-gray-100 text-xs text-gray-500">
                Hit <strong class="text-gray-700">{{ number_format($redirect->hits) }}</strong> {{ Str::plural('time', $redirect->hits) }}
                @if($redirect->last_hit_at)
                    — last {{ $redirect->last_hit_at->diffForHumans() }}
                @endif
            </div>
            @endif

        </div>

        <div class="flex items-center justify-end gap-3 px-5 py-3 bg-gray-50 border-t border-gray-100">
            <a href="{{ route('cms.admin.redirects.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                {{ $redirect->exists ? 'Save changes' : 'Create redirect' }}
            </button>
        </div>
    </form>

</div>

@endsection
