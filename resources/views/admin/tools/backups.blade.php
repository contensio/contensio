{{--
 | Contensio - The open content platform for Laravel.
 | Admin — Tools / Backups.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('contensio::admin.layout')

@section('title', 'Backups')

@section('breadcrumb')
<span class="font-medium text-gray-700">Tools</span>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Backups</span>
@endsection

@section('content')

<div class="max-w-4xl mx-auto">

    <h1 class="text-xl font-bold text-gray-900 mb-1">Backups</h1>
    <p class="text-sm text-gray-500 mb-5">Create and restore complete site backups — database and media files.</p>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <i class="bi bi-check-circle-fill text-green-600 shrink-0"></i>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->has('backup'))
    <div class="mb-5 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        {{ $errors->first('backup') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">

        {{-- ─── Create backup ───────────────────────────────────────────── --}}
        <form method="POST" action="{{ route('contensio.account.tools.backups.store') }}"
              class="bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            @csrf

            <div class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-lg shrink-0 bg-blue-600">
                    <i class="bi bi-archive"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-bold text-gray-900">Create backup</h2>
                    <p class="text-xs text-gray-500">Save a snapshot of your database and files.</p>
                </div>
            </div>

            <div class="p-5 space-y-4 flex-1">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="include_files" value="1" checked
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">
                        <strong>Include media files</strong>
                        <span class="block text-xs text-gray-500">Includes everything in <code>storage/app/</code> — images, documents, uploads.</span>
                    </span>
                </label>

                <div class="text-xs text-gray-400 bg-gray-50 rounded-lg px-4 py-3 leading-relaxed">
                    The backup is stored in <code>storage/app/backups/</code>.<br>
                    Download it after creation and store it somewhere safe.
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                    <i class="bi bi-archive text-sm"></i>
                    Create backup
                </button>
            </div>
        </form>

        {{-- ─── Restore from file ───────────────────────────────────────── --}}
        <form method="POST" action="{{ route('contensio.account.tools.backups.restore-upload') }}"
              enctype="multipart/form-data"
              class="bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col">
            @csrf

            <div class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-lg shrink-0 bg-amber-500">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-bold text-gray-900">Restore from file</h2>
                    <p class="text-xs text-gray-500">Upload a Contensio backup ZIP to restore.</p>
                </div>
            </div>

            <div class="p-5 space-y-4 flex-1">

                @if($errors->has('backup_file'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm">
                    {{ $errors->first('backup_file') }}
                </div>
                @endif

                <div>
                    <label class="block text-sm text-gray-700 font-medium mb-1.5">Backup file (.zip)</label>
                    <input type="file" name="backup_file" accept=".zip" required
                           class="block w-full text-sm text-gray-500 border border-gray-300 rounded-lg cursor-pointer
                                  file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0
                                  file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700
                                  hover:file:bg-gray-100 focus:outline-none">
                </div>

                <div class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 leading-relaxed">
                    <strong>Warning:</strong> Restoring will overwrite your current database and media files.
                    Make a backup first if needed.
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                    <i class="bi bi-upload text-sm"></i>
                    Upload &amp; validate
                </button>
            </div>
        </form>

    </div>

    {{-- ─── Backup list ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">Stored backups</h2>
                <p class="text-xs text-gray-500 mt-0.5">Backups saved in <code>storage/app/backups/</code>.</p>
            </div>
            <span class="text-xs text-gray-400">{{ count($backups) }} {{ Str::plural('backup', count($backups)) }}</span>
        </div>

        @if(empty($backups))
        <div class="px-5 py-10 text-center text-sm text-gray-400">
            <i class="bi bi-archive text-3xl text-gray-300 block mb-2"></i>
            No backups yet. Create your first backup above.
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($backups as $backup)
            <div class="flex items-center gap-4 px-5 py-4">

                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0
                            {{ $backup['includes_files'] ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-500' }}">
                    <i class="bi {{ $backup['includes_files'] ? 'bi-archive-fill' : 'bi-database' }} text-base"></i>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-medium text-gray-900 truncate">{{ $backup['filename'] }}</span>
                        @if($backup['includes_files'])
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">Full</span>
                        @else
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">DB only</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                        <span>{{ $backup['size_human'] }}</span>
                        <span>&middot;</span>
                        <span>{{ $backup['table_count'] }} tables</span>
                        @if($backup['includes_files'])
                        <span>&middot;</span>
                        <span>{{ number_format($backup['file_count']) }} files</span>
                        @endif
                        <span>&middot;</span>
                        <span>v{{ $backup['cms_version'] }}</span>
                        <span>&middot;</span>
                        <span title="{{ $backup['created_at'] }}">
                            {{ \Carbon\Carbon::parse($backup['created_at'])->diffForHumans() }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('contensio.account.tools.backups.download', $backup['filename']) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-800
                              border border-blue-200 hover:border-blue-400 bg-blue-50 hover:bg-blue-100
                              px-3 py-1.5 rounded-lg transition-colors">
                        <i class="bi bi-download"></i>
                        Download
                    </a>

                    <form method="POST"
                          action="{{ route('contensio.account.tools.backups.destroy', $backup['filename']) }}"
                          onsubmit="return confirm('Delete this backup? This cannot be undone.')"
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 hover:text-red-800
                                       border border-red-200 hover:border-red-400 bg-red-50 hover:bg-red-100
                                       px-3 py-1.5 rounded-lg transition-colors">
                            <i class="bi bi-trash3"></i>
                            Delete
                        </button>
                    </form>
                </div>

            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- CLI tip --}}
    <div class="mt-5 bg-gray-50 border border-gray-200 rounded-xl px-5 py-4">
        <p class="text-sm font-medium text-gray-700 mb-2">Run from the command line</p>
        <div class="space-y-1.5">
            <div class="flex items-center gap-3 font-mono text-xs text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2.5">
                <span class="text-gray-400">$</span>
                <code>php artisan contensio:backup</code>
                <span class="ml-auto text-gray-400">Full backup</span>
            </div>
            <div class="flex items-center gap-3 font-mono text-xs text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2.5">
                <span class="text-gray-400">$</span>
                <code>php artisan contensio:backup --no-files</code>
                <span class="ml-auto text-gray-400">Database only</span>
            </div>
            <div class="flex items-center gap-3 font-mono text-xs text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2.5">
                <span class="text-gray-400">$</span>
                <code>php artisan contensio:restore path/to/backup.zip</code>
                <span class="ml-auto text-gray-400">Restore</span>
            </div>
        </div>
    </div>

</div>

@endsection
