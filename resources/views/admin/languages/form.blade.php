{{--
 | Contensio - The open content platform for Laravel.
 | Admin — languages form.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', $language ? 'Edit Language' : 'Add Language')

@section('breadcrumb')
    <a href="{{ route('cms.admin.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('cms.admin.languages.index') }}" class="text-gray-500 hover:text-gray-700">Languages</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">{{ $language ? 'Edit' : 'Add Language' }}</span>
@endsection

@section('content')

<div class="max-w-md">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">{{ $language ? 'Edit Language' : 'Add Language' }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ $language ? 'Update language details.' : 'Add a new language to your site.' }}
        </p>
    </div>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
        <ul class="text-sm text-red-700 space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $language ? route('cms.admin.languages.update', $language->id) : route('cms.admin.languages.store') }}">
        @csrf
        @if($language) @method('PUT') @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
            <div class="px-5 py-5 space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Language name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $language?->name ?? '') }}"
                           placeholder="e.g. English, French, Arabic"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-400 @enderror"
                           required>
                    @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Language code <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="code"
                           value="{{ old('code', $language?->code ?? '') }}"
                           placeholder="e.g. en, fr, pt-BR"
                           maxlength="5"
                           {{ $language ? 'readonly' : '' }}
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('code') border-red-400 @enderror {{ $language ? 'bg-gray-50 text-gray-500' : '' }}"
                           required>
                    <p class="mt-1 text-xs text-gray-400">
                        ISO 639-1 code (e.g. <code class="font-mono">en</code>, <code class="font-mono">fr</code>, <code class="font-mono">pt-BR</code>).
                        {{ $language ? 'Code cannot be changed after creation.' : '' }}
                    </p>
                    @error('code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Text direction</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="direction" value="ltr"
                                   {{ old('direction', $language?->direction ?? 'ltr') === 'ltr' ? 'checked' : '' }}
                                   class="w-4 h-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">LTR <span class="text-gray-400 text-xs">(Left to right)</span></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="direction" value="rtl"
                                   {{ old('direction', $language?->direction ?? 'ltr') === 'rtl' ? 'checked' : '' }}
                                   class="w-4 h-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">RTL <span class="text-gray-400 text-xs">(Right to left)</span></span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        {{-- Status --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-100 bg-white">
                <h2 class="text-sm font-semibold text-gray-700">Status</h2>
            </div>
            <div class="px-5 py-4 space-y-3">

                @php $currentStatus = old('status', $language?->status ?? 'active'); @endphp

                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                              {{ $currentStatus === 'active' ? 'border-blue-300 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}">
                    <input type="radio" name="status" value="active"
                           {{ $currentStatus === 'active' ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-800">Active</p>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Live</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Visible in the admin panel and on the public website.</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                              {{ $currentStatus === 'inactive' ? 'border-amber-300 bg-amber-50' : 'border-gray-200 hover:bg-gray-50' }}">
                    <input type="radio" name="status" value="inactive"
                           {{ $currentStatus === 'inactive' ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-800">Inactive</p>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">Admin only</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Visible in the admin panel only. Use this while preparing content before going live.</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                              {{ $currentStatus === 'disabled' ? 'border-gray-400 bg-gray-100' : 'border-gray-200 hover:bg-gray-50' }}
                              {{ $language?->is_default ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <input type="radio" name="status" value="disabled"
                           {{ $currentStatus === 'disabled' ? 'checked' : '' }}
                           {{ $language?->is_default ? 'disabled' : '' }}
                           class="mt-0.5 w-4 h-4 border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-800">Disabled</p>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-600">Hidden</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Hidden from admin content editors and the public website.
                            {{ $language?->is_default ? 'Cannot disable the default language.' : '' }}
                        </p>
                    </div>
                </label>

                @error('status')
                <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-5 py-2.5 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $language ? 'Save Changes' : 'Add Language' }}
            </button>
            <a href="{{ route('cms.admin.languages.index') }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-100 transition-colors">
                Cancel
            </a>
        </div>

    </form>
</div>

@endsection
