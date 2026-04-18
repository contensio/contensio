{{--
 | Contensio - The open content platform for Laravel.
 | Admin — contact messages inbox.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', __('contensio::admin.contact.messages.title'))

@section('breadcrumb')
    <a href="{{ route('contensio.account.contact.index') }}" class="text-gray-500 hover:text-gray-700">
        {{ __('contensio::admin.contact.title') }}
    </a>
    <span class="mx-1.5 text-gray-400">/</span>
    <span class="text-gray-900 font-medium">{{ __('contensio::admin.contact.messages.title') }}</span>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-3">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-md px-4 py-3">
    {{ session('error') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-900">{{ __('contensio::admin.contact.messages.title') }}</h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ __('contensio::admin.contact.messages.subtitle') }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('contensio.account.contact.labels.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="bi bi-tags text-sm"></i>
            {{ __('contensio::admin.contact.messages.manage_labels') }}
        </a>
        <a href="{{ route('contensio.account.contact.messages.export', array_filter(['status' => $status, 'label' => $label])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="bi bi-download text-sm"></i>
            {{ __('contensio::admin.contact.messages.export_csv') }}
        </a>
        <a href="{{ route('contensio.account.contact.index') }}"
           class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-4 py-2 rounded-md transition-colors shadow-sm">
            <i class="bi bi-gear text-sm"></i>
            {{ __('contensio::admin.contact.messages.settings_btn') }}
        </a>
    </div>
</div>

{{-- Status tabs --}}
<div class="flex items-center gap-1 mb-3 border-b border-gray-200 overflow-x-auto">
    @php
        $tabs = [
            'all'     => __('contensio::admin.contact.messages.tab_all'),
            'new'     => __('contensio::admin.contact.messages.tab_new'),
            'read'    => __('contensio::admin.contact.messages.tab_read'),
            'replied' => __('contensio::admin.contact.messages.tab_replied'),
            'spam'    => __('contensio::admin.contact.messages.tab_spam'),
        ];
    @endphp
    @foreach($tabs as $tabKey => $tabLabel)
    @php $tabCount = $counts[$tabKey] ?? 0; @endphp
    <a href="{{ route('contensio.account.contact.messages.index', array_filter(['status' => $tabKey, 'q' => $search, 'label' => $label])) }}"
       class="shrink-0 inline-flex items-center gap-1.5 px-3 pb-3 pt-1 text-sm font-medium border-b-2 transition-colors
              {{ $status === $tabKey
                  ? 'border-ember-500 text-ember-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
        {{ $tabLabel }}
        @if($tabCount > 0)
        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-semibold
                     {{ $status === $tabKey ? 'bg-ember-100 text-ember-700' : 'bg-gray-100 text-gray-600' }}">
            {{ $tabCount }}
        </span>
        @endif
    </a>
    @endforeach
</div>

{{-- Label filter bar (only when labels exist) --}}
@if($allLabels->isNotEmpty())
<div class="flex items-center gap-2 mb-4 flex-wrap">
    <span class="text-xs text-gray-400 font-medium shrink-0">{{ __('contensio::admin.contact.messages.filter_label') }}</span>
    <a href="{{ route('contensio.account.contact.messages.index', array_filter(['status' => $status, 'q' => $search])) }}"
       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition-colors
              {{ !$label ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
        {{ __('contensio::admin.contact.messages.filter_all') }}
    </a>
    @foreach($allLabels as $lbl)
    <a href="{{ route('contensio.account.contact.messages.index', array_filter(['status' => $status, 'q' => $search, 'label' => $lbl->slug])) }}"
       class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-colors
              {{ $label === $lbl->slug ? 'text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
       style="{{ $label === $lbl->slug ? 'background-color:' . $lbl->color : '' }}">
        <span class="w-2 h-2 rounded-full shrink-0 {{ $label === $lbl->slug ? 'bg-white/60' : '' }}"
              style="{{ $label !== $lbl->slug ? 'background-color:' . $lbl->color : '' }}"></span>
        {{ $lbl->name }}
    </a>
    @endforeach
</div>
@endif

{{-- Search --}}
<form method="GET" action="{{ route('contensio.account.contact.messages.index') }}" class="mb-4 flex gap-2">
    <input type="hidden" name="status" value="{{ $status }}">
    @if($label)<input type="hidden" name="label" value="{{ $label }}">@endif
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('contensio::admin.contact.messages.search_placeholder') }}"
               class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
    </div>
    <button type="submit"
            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
        {{ __('contensio::admin.contact.messages.search_btn') }}
    </button>
    @if($search)
    <a href="{{ route('contensio.account.contact.messages.index', array_filter(['status' => $status, 'label' => $label])) }}"
       class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
        {{ __('contensio::admin.contact.messages.clear') }}
    </a>
    @endif
</form>

@if($messages->isEmpty())

<div class="bg-white border border-gray-200 rounded-lg p-16 text-center">
    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="bi bi-envelope text-2xl text-gray-400"></i>
    </div>
    <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ __('contensio::admin.contact.messages.empty_title') }}</h3>
    <p class="text-sm text-gray-400">
        @if($search || $label)
            {{ __('contensio::admin.contact.messages.empty_search') }}
        @else
            {{ __('contensio::admin.contact.messages.empty_subtitle') }}
        @endif
    </p>
</div>

@else

{{-- Per-row delete forms --}}
@foreach($messages as $msg)
    <form id="delete-msg-{{ $msg->id }}" method="POST" action="{{ route('contensio.account.contact.messages.destroy', $msg->id) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endforeach

{{-- Bulk assign form (separate from bulk-msg-form to avoid nesting) --}}
@if($allLabels->isNotEmpty())
<form id="bulk-label-form" method="POST" action="{{ route('contensio.account.contact.labels.bulk-assign') }}" class="hidden">
    @csrf
    <div id="bulk-label-ids"></div>
    <input type="hidden" name="label_id" id="bulk-label-id-value">
</form>
@endif

<form id="bulk-msg-form" method="POST" action="{{ route('contensio.account.contact.messages.bulk') }}">
    @csrf
    <input type="hidden" name="action" id="bulk-msg-action">

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden"
         x-data="{
             noneSelected: false,
             showLabelPicker: false,
             confirmBulk(action, title, description, confirmLabel) {
                 const checked = document.querySelectorAll('.row-check-msg:checked');
                 if (checked.length === 0) {
                     this.noneSelected = true;
                     setTimeout(() => this.noneSelected = false, 2500);
                     return;
                 }
                 this.noneSelected = false;
                 document.getElementById('bulk-msg-action').value = action;
                 $dispatch('cms:confirm', { title, description, confirmLabel, formId: 'bulk-msg-form' });
             },
             assignLabelBulk(labelId) {
                 const checked = document.querySelectorAll('.row-check-msg:checked');
                 if (checked.length === 0) {
                     this.noneSelected = true;
                     this.showLabelPicker = false;
                     setTimeout(() => this.noneSelected = false, 2500);
                     return;
                 }
                 // Populate hidden label form with selected IDs
                 const container = document.getElementById('bulk-label-ids');
                 container.innerHTML = '';
                 checked.forEach(cb => {
                     const input = document.createElement('input');
                     input.type = 'hidden';
                     input.name = 'ids[]';
                     input.value = cb.value;
                     container.appendChild(input);
                 });
                 document.getElementById('bulk-label-id-value').value = labelId;
                 document.getElementById('bulk-label-form').submit();
             }
         }">

        {{-- Bulk toolbar --}}
        <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-100 bg-gray-50/50 flex-wrap">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" id="select-all-msg"
                       class="w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500"
                       @change="document.querySelectorAll('.row-check-msg').forEach(cb => cb.checked = $event.target.checked)">
                <span class="text-xs font-medium text-gray-500">{{ __('contensio::admin.contact.messages.select_all') }}</span>
            </label>
            <span x-show="noneSelected" x-cloak class="text-xs text-amber-600 font-medium">
                {{ __('contensio::admin.contact.messages.select_first') }}
            </span>
            <div class="flex items-center gap-1 ml-2 flex-wrap">
                @if($status !== 'read')
                <button type="button"
                        @click="confirmBulk('mark_read', '{{ __('contensio::admin.contact.messages.confirm_mark_read') }}', '', '{{ __('contensio::admin.contact.messages.mark_read') }}')"
                        class="text-xs font-medium text-gray-600 hover:text-blue-700 border border-gray-200 hover:border-blue-300 bg-white hover:bg-blue-50 rounded px-2.5 py-1 transition-colors">
                    {{ __('contensio::admin.contact.messages.mark_read') }}
                </button>
                @endif
                @if($status !== 'replied')
                <button type="button"
                        @click="confirmBulk('mark_replied', '{{ __('contensio::admin.contact.messages.confirm_mark_replied') }}', '', '{{ __('contensio::admin.contact.messages.mark_replied') }}')"
                        class="text-xs font-medium text-gray-600 hover:text-green-700 border border-gray-200 hover:border-green-300 bg-white hover:bg-green-50 rounded px-2.5 py-1 transition-colors">
                    {{ __('contensio::admin.contact.messages.mark_replied') }}
                </button>
                @endif
                @if($status !== 'spam')
                <button type="button"
                        @click="confirmBulk('mark_spam', '{{ __('contensio::admin.contact.messages.confirm_mark_spam') }}', '', '{{ __('contensio::admin.contact.messages.mark_spam') }}')"
                        class="text-xs font-medium text-gray-600 hover:text-amber-700 border border-gray-200 hover:border-amber-300 bg-white hover:bg-amber-50 rounded px-2.5 py-1 transition-colors">
                    {{ __('contensio::admin.contact.messages.mark_spam') }}
                </button>
                @endif

                {{-- Label picker button --}}
                @if($allLabels->isNotEmpty())
                <div class="relative" x-data>
                    <button type="button" @click="$root.showLabelPicker = !$root.showLabelPicker"
                            class="text-xs font-medium text-gray-600 hover:text-indigo-700 border border-gray-200 hover:border-indigo-300 bg-white hover:bg-indigo-50 rounded px-2.5 py-1 transition-colors inline-flex items-center gap-1">
                        <i class="bi bi-tag text-xs"></i>
                        {{ __('contensio::admin.contact.messages.assign_label') }}
                        <i class="bi bi-chevron-down text-[10px]"></i>
                    </button>
                    <div x-show="$root.showLabelPicker" x-cloak @click.outside="$root.showLabelPicker = false"
                         class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 min-w-[160px] py-1">
                        @foreach($allLabels as $lbl)
                        <button type="button" @click="assignLabelBulk({{ $lbl->id }}); $root.showLabelPicker = false"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                  style="background-color: {{ $lbl->color }}"></span>
                            {{ $lbl->name }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                <button type="button"
                        @click="confirmBulk('delete', '{{ __('contensio::admin.contact.messages.confirm_delete_bulk') }}', '{{ __('contensio::admin.contact.messages.confirm_delete_bulk_desc') }}', '{{ __('contensio::admin.contact.messages.delete') }}')"
                        class="text-xs font-medium text-gray-600 hover:text-red-700 border border-gray-200 hover:border-red-300 bg-white hover:bg-red-50 rounded px-2.5 py-1 transition-colors">
                    {{ __('contensio::admin.contact.messages.delete') }}
                </button>
            </div>
        </div>

        {{-- Message list --}}
        <div class="divide-y divide-gray-100">
            @foreach($messages as $msg)
            @php
                $statusColors = [
                    'new'     => 'bg-blue-100 text-blue-700',
                    'read'    => 'bg-gray-100 text-gray-600',
                    'replied' => 'bg-green-100 text-green-700',
                    'spam'    => 'bg-red-100 text-red-600',
                ];
            @endphp
            <div class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50/50 transition-colors {{ $msg->status === 'new' ? 'font-semibold' : '' }}">
                <div class="pt-0.5">
                    <input type="checkbox" name="ids[]" value="{{ $msg->id }}"
                           class="row-check-msg w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-500">
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('contensio.account.contact.messages.show', $msg->id) }}" class="block group">
                        <div class="flex items-center justify-between gap-2 mb-0.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-sm text-gray-900 truncate group-hover:text-ember-600 transition-colors">
                                    {{ $msg->name }}
                                </span>
                                <span class="text-xs text-gray-400 truncate hidden sm:inline">{{ $msg->email }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if($msg->files->isNotEmpty())
                                <i class="bi bi-paperclip text-xs text-gray-400" title="{{ $msg->files->count() }} attachment(s)"></i>
                                @endif
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $statusColors[$msg->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($msg->status) }}
                                </span>
                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                    {{ $msg->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        @if($msg->subject)
                        <p class="text-sm text-gray-700 truncate">{{ $msg->subject }}</p>
                        @endif
                        <p class="text-sm text-gray-400 truncate mt-0.5">{{ Str::limit($msg->message, 120) }}</p>
                    </a>
                    {{-- Label chips --}}
                    @if($msg->labels->isNotEmpty())
                    <div class="flex items-center gap-1 mt-1.5 flex-wrap">
                        @foreach($msg->labels as $lbl)
                        <a href="{{ route('contensio.account.contact.messages.index', array_filter(['status' => $status, 'q' => $search, 'label' => $lbl->slug])) }}"
                           class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium text-white transition-opacity hover:opacity-80"
                           style="background-color: {{ $lbl->color }}">
                            {{ $lbl->name }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="pt-0.5">
                    <button type="button"
                            @click="$dispatch('cms:confirm', {
                                title: '{{ __('contensio::admin.contact.messages.confirm_delete_one') }}',
                                description: '{{ __('contensio::admin.contact.messages.confirm_delete_one_desc') }}',
                                confirmLabel: '{{ __('contensio::admin.contact.messages.delete') }}',
                                formId: 'delete-msg-{{ $msg->id }}'
                            })"
                            class="text-gray-300 hover:text-red-500 transition-colors p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</form>

{{-- Pagination --}}
@if($messages->hasPages())
<div class="mt-4">
    {{ $messages->links() }}
</div>
@endif

@endif

@endsection
