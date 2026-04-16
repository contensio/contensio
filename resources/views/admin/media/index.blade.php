{{--
 | Contensio - The open content platform for Laravel.
 | Admin — media index.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Media Library')

@section('breadcrumb')
    <span class="text-gray-900 font-medium">Media Library</span>
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

<div x-data="{ uploading: false }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Media Library</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $items->total() }} {{ Str::plural('file', $items->total()) }}</p>
        </div>
        <button type="button"
                @click="uploading = true"
                class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-medium text-sm px-4 py-2 rounded-md transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload Files
        </button>
    </div>

    {{-- Upload modal --}}
    <div x-show="uploading"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="uploading = false"></div>

        <div x-show="uploading"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md"
             @click.stop>

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Upload Files</h3>
                <button type="button" @click="uploading = false"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST"
                  action="{{ route('contensio.account.media.upload') }}"
                  enctype="multipart/form-data"
                  class="p-6">
                @csrf

                <div x-data="{ files: [] }"
                     class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition-colors">
                    <input type="file"
                           name="files[]"
                           multiple
                           accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                           class="hidden"
                           id="file-input"
                           @change="files = Array.from($event.target.files).map(f => f.name)">
                    <label for="file-input" class="cursor-pointer block">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-700 mb-1">Click to choose files</p>
                        <p class="text-xs text-gray-400">Images, PDFs, documents — up to 20 MB each</p>
                    </label>
                    <template x-if="files.length">
                        <ul class="mt-4 text-left space-y-1">
                            <template x-for="f in files" :key="f">
                                <li class="flex items-center gap-2 text-xs text-gray-600">
                                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span x-text="f" class="truncate"></span>
                                </li>
                            </template>
                        </ul>
                    </template>
                </div>

                <div class="flex justify-end gap-3 mt-5">
                    <button type="button"
                            @click="uploading = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-white bg-ember-500 hover:bg-ember-600 rounded-md transition-colors shadow-sm">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Grid --}}
    @if($items->isEmpty())
    <div class="bg-white border border-gray-200 rounded-md p-16 text-center">
        <div class="w-12 h-12 bg-gray-100 rounded-md flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h3 class="text-sm font-semibold text-gray-900 mb-1">No files yet</h3>
        <p class="text-sm text-gray-400 mb-5">Upload your first image or document.</p>
        <button type="button"
                @click="uploading = true"
                class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-medium text-sm px-4 py-2 rounded-md transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Upload Files
        </button>
    </div>

    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @foreach($items as $item)
        @php
            $isImage  = $item->isImage();
            $url      = Storage::disk($item->disk)->url($item->file_path);
            $sizeText = $item->file_size >= 1048576
                ? round($item->file_size / 1048576, 1) . ' MB'
                : round($item->file_size / 1024) . ' KB';
            $ext = strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION));
        @endphp

        <div class="group relative bg-white rounded-lg border border-gray-200 overflow-hidden hover:border-blue-300 hover:shadow-sm transition-all">

            {{-- Thumbnail --}}
            <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
                @if($isImage)
                <img src="{{ $url }}" alt="{{ $item->file_name }}"
                     class="w-full h-full object-cover">
                @else
                @php
                    $iconColor = match(true) {
                        str_contains($item->mime_type, 'pdf')        => 'text-red-500',
                        str_contains($item->mime_type, 'word')       => 'text-blue-500',
                        str_contains($item->mime_type, 'sheet')      => 'text-green-500',
                        str_contains($item->mime_type, 'video')      => 'text-purple-500',
                        str_contains($item->mime_type, 'audio')      => 'text-orange-500',
                        str_contains($item->mime_type, 'zip')        => 'text-yellow-600',
                        default                                       => 'text-gray-400',
                    };
                @endphp
                <div class="flex flex-col items-center gap-1">
                    <svg class="w-9 h-9 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-[10px] font-bold text-gray-400 uppercase">{{ $ext }}</span>
                </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="p-2">
                <p class="text-xs text-gray-700 truncate font-medium" title="{{ $item->file_name }}">
                    {{ $item->file_name }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $sizeText }}</p>
            </div>

            {{-- Delete overlay --}}
            <div class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                <form id="delete-media-{{ $item->id }}"
                      method="POST"
                      action="{{ route('contensio.account.media.destroy', $item->id) }}"
                      class="hidden">
                    @csrf @method('DELETE')
                </form>
                <button type="button"
                        @click="$dispatch('cms:confirm', {
                            title: 'Delete file?',
                            description: 'This will permanently delete &quot;{{ addslashes($item->file_name) }}&quot; from the server.',
                            confirmLabel: 'Delete',
                            formId: 'delete-media-{{ $item->id }}'
                        })"
                        class="w-6 h-6 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center shadow-sm transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div class="mt-6">
        {{ $items->links() }}
    </div>
    @endif

    @endif

</div>

@endsection
