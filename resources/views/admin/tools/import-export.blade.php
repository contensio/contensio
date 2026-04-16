{{--
 | Contensio - The open content platform for Laravel.
 | Admin — Tools / Import & Export.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('contensio::admin.layout')

@section('title', 'Import / Export')

@section('breadcrumb')
<span class="font-medium text-gray-700">Tools</span>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Import / Export</span>
@endsection

@section('content')

<div class="max-w-4xl mx-auto">

    <h1 class="text-xl font-bold text-gray-900 mb-1">Import / Export</h1>
    <p class="text-sm text-gray-500 mb-5">Move content between Contensio sites — back up, migrate dev → prod, or clone a site.</p>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <i class="bi bi-check-circle-fill text-green-600"></i>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        @foreach($errors->all() as $err)
        <p>{{ $err }}</p>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- ─── Export ─────────────────────────────────────────────────── --}}
        <form method="POST" action="{{ route('contensio.account.tools.export') }}"
              class="bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            @csrf

            <div class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-lg shrink-0 bg-blue-600">
                    <i class="bi bi-download"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-bold text-gray-900">Export</h2>
                    <p class="text-xs text-gray-500">Download all content as a single JSON file.</p>
                </div>
            </div>

            <div class="p-5 space-y-4 flex-1">
                <p class="text-xs text-gray-500">Choose what to include:</p>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="include_content" value="1" checked
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
                    <span class="text-sm text-gray-700">
                        <strong>Content</strong> — {{ $stats['pages'] }} {{ Str::plural('page', $stats['pages']) }}, {{ $stats['posts'] }} {{ Str::plural('post', $stats['posts']) }}
                        <span class="block text-xs text-gray-500">with translations, blocks, and meta</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="include_menus" value="1" checked
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
                    <span class="text-sm text-gray-700">
                        <strong>Menus</strong> — {{ $stats['menus'] }} {{ Str::plural('menu', $stats['menus']) }}
                        <span class="block text-xs text-gray-500">with items and translations</span>
                    </span>
                </label>

                <div class="text-xs text-gray-500 pt-2 border-t border-gray-100">
                    <i class="bi bi-info-circle"></i>
                    Media files and site settings are not included in this version.
                </div>
            </div>

            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                <button type="submit"
                        class="w-full bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                    Download export
                </button>
            </div>
        </form>

        {{-- ─── Import ─────────────────────────────────────────────────── --}}
        <form method="POST" action="{{ route('contensio.account.tools.import') }}"
              enctype="multipart/form-data"
              class="bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            @csrf

            <div class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-lg shrink-0 bg-emerald-600">
                    <i class="bi bi-upload"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-bold text-gray-900">Import</h2>
                    <p class="text-xs text-gray-500">Restore content from a Contensio export file.</p>
                </div>
            </div>

            <div class="p-5 space-y-4 flex-1">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Export file</label>
                    <input type="file" name="file" accept=".json,application/json" required
                           class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                  file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700
                                  hover:file:bg-emerald-100 cursor-pointer">
                    <p class="mt-1 text-xs text-gray-500">Max 20 MB. JSON only.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">On conflict</label>
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="radio" name="conflict" value="skip" checked
                                   class="mt-0.5 w-4 h-4 border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm text-gray-700">
                                <strong>Skip</strong> existing items <span class="text-xs text-gray-500">(safer)</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="radio" name="conflict" value="overwrite"
                                   class="mt-0.5 w-4 h-4 border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm text-gray-700">
                                <strong>Overwrite</strong> existing items <span class="text-xs text-gray-500">(matched by slug / name)</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                <button type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                    Run import
                </button>
            </div>
        </form>

    </div>

</div>

@endsection
