{{--
 | Contensio - The open content platform for Laravel.
 | Admin — Tools / Backup restore confirmation.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('contensio::admin.layout')

@section('title', 'Confirm Restore')

@section('breadcrumb')
<span class="font-medium text-gray-700">Tools</span>
<span class="mx-2 text-gray-300">/</span>
<a href="{{ route('contensio.account.tools.backups') }}" class="font-medium text-gray-700 hover:text-gray-900">Backups</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Confirm Restore</span>
@endsection

@section('content')

<div class="max-w-2xl mx-auto">

    <h1 class="text-xl font-bold text-gray-900 mb-1">Confirm restore</h1>
    <p class="text-sm text-gray-500 mb-6">Review the backup details below, then enter your admin password to proceed.</p>

    {{-- Warning banner --}}
    <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-300 text-red-800 rounded-xl px-5 py-4">
        <i class="bi bi-exclamation-triangle-fill text-red-500 text-lg shrink-0 mt-0.5"></i>
        <div class="text-sm leading-relaxed">
            <strong class="block mb-1">This will overwrite your current site.</strong>
            All database tables will be dropped and restored from this backup.
            @if($manifest['includes_files'] ?? false)
            Media files in <code>storage/app/</code> will also be overwritten.
            @endif
            <strong>This cannot be undone.</strong>
        </div>
    </div>

    {{-- Manifest card --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-3">
            <i class="bi bi-archive text-blue-600 text-lg"></i>
            <h2 class="text-base font-bold text-gray-900">Backup details</h2>
        </div>
        <dl class="divide-y divide-gray-100">
            <div class="flex items-center px-5 py-3 gap-4">
                <dt class="w-36 shrink-0 text-sm text-gray-500">Site URL</dt>
                <dd class="text-sm text-gray-900 font-medium">{{ $manifest['site_url'] ?? '—' }}</dd>
            </div>
            <div class="flex items-center px-5 py-3 gap-4">
                <dt class="w-36 shrink-0 text-sm text-gray-500">Created</dt>
                <dd class="text-sm text-gray-900">
                    @if(!empty($manifest['created_at']))
                        {{ \Carbon\Carbon::parse($manifest['created_at'])->format('d M Y, H:i') }}
                        <span class="text-gray-400">({{ \Carbon\Carbon::parse($manifest['created_at'])->diffForHumans() }})</span>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="flex items-center px-5 py-3 gap-4">
                <dt class="w-36 shrink-0 text-sm text-gray-500">CMS version</dt>
                <dd class="text-sm text-gray-900">{{ $manifest['cms_version'] ?? '—' }}</dd>
            </div>
            <div class="flex items-center px-5 py-3 gap-4">
                <dt class="w-36 shrink-0 text-sm text-gray-500">Database tables</dt>
                <dd class="text-sm text-gray-900">{{ $manifest['table_count'] ?? 0 }}</dd>
            </div>
            @if($manifest['includes_files'] ?? false)
            <div class="flex items-center px-5 py-3 gap-4">
                <dt class="w-36 shrink-0 text-sm text-gray-500">Media files</dt>
                <dd class="text-sm text-gray-900">{{ number_format($manifest['file_count'] ?? 0) }}</dd>
            </div>
            @endif
            <div class="flex items-center px-5 py-3 gap-4">
                <dt class="w-36 shrink-0 text-sm text-gray-500">Includes files</dt>
                <dd class="text-sm">
                    @if($manifest['includes_files'] ?? false)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                        <i class="bi bi-check-circle-fill"></i> Yes — full backup
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                        Database only
                    </span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    {{-- Password form --}}
    <form method="POST" action="{{ route('contensio.account.tools.backups.restore-execute') }}"
          class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @csrf

        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Confirm with your password</h2>
            <p class="text-xs text-gray-500 mt-0.5">Enter your admin account password to authorise the restore.</p>
        </div>

        <div class="p-5">

            @if($errors->has('password'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm">
                {{ $errors->first('password') }}
            </div>
            @endif

            @if($errors->has('backup'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-2.5 text-sm">
                {{ $errors->first('backup') }}
            </div>
            @endif

            <label class="block text-sm font-medium text-gray-700 mb-1.5">Admin password</label>
            <input type="password" name="password" autocomplete="current-password" required
                   autofocus
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-900
                          focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500
                          @error('password') border-red-400 @enderror">
        </div>

        <div class="px-5 py-4 border-t border-gray-100 flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                <i class="bi bi-arrow-counterclockwise"></i>
                Restore now
            </button>
            <a href="{{ route('contensio.account.tools.backups') }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-lg transition-colors">
                Cancel
            </a>
        </div>

    </form>

</div>

@endsection
