{{--
 | Contensio - The open content platform for Laravel.
 | Admin — contact message detail.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', __('contensio::admin.contact.messages.detail_title'))

@section('breadcrumb')
    <a href="{{ route('contensio.account.contact.index') }}" class="text-gray-500 hover:text-gray-700">
        {{ __('contensio::admin.contact.title') }}
    </a>
    <span class="mx-1.5 text-gray-400">/</span>
    <a href="{{ route('contensio.account.contact.messages.index') }}" class="text-gray-500 hover:text-gray-700">
        {{ __('contensio::admin.contact.messages.title') }}
    </a>
    <span class="mx-1.5 text-gray-400">/</span>
    <span class="text-gray-900 font-medium">{{ $message->name }}</span>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md px-4 py-3">
    {{ session('success') }}
</div>
@endif

{{-- Status update + delete forms --}}
<form id="status-form" method="POST" action="{{ route('contensio.account.contact.messages.bulk') }}" class="hidden">
    @csrf
    <input type="hidden" name="ids[]" value="{{ $message->id }}">
    <input type="hidden" name="action" id="status-action-value">
</form>
<form id="delete-form" method="POST" action="{{ route('contensio.account.contact.messages.destroy', $message->id) }}" class="hidden">
    @csrf @method('DELETE')
</form>

<div class="max-w-3xl"
     x-data="{
         labels: @json($message->labels->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->values()),
         allLabels: @json($allLabels->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->values()),
         showPicker: false,
         loading: false,
         csrfToken: document.querySelector('meta[name=csrf-token]').content,
         hasLabel(id) {
             return this.labels.some(l => l.id === id);
         },
         async addLabel(label) {
             if (this.hasLabel(label.id) || this.loading) return;
             this.loading = true;
             try {
                 const res = await fetch('{{ route('contensio.account.contact.messages.labels.attach', $message->id) }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': this.csrfToken,
                         'Accept': 'application/json',
                     },
                     body: JSON.stringify({ label_id: label.id }),
                 });
                 if (res.ok) {
                     this.labels.push(label);
                     this.showPicker = false;
                 }
             } finally {
                 this.loading = false;
             }
         },
         async removeLabel(labelId) {
             if (this.loading) return;
             this.loading = true;
             try {
                 const res = await fetch(`{{ url(route('contensio.account.contact.messages.labels.attach', $message->id)) }}/${labelId}`, {
                     method: 'DELETE',
                     headers: {
                         'X-CSRF-TOKEN': this.csrfToken,
                         'Accept': 'application/json',
                     },
                 });
                 if (res.ok) {
                     this.labels = this.labels.filter(l => l.id !== labelId);
                 }
             } finally {
                 this.loading = false;
             }
         },
         get availableLabels() {
             return this.allLabels.filter(l => !this.hasLabel(l.id));
         }
     }">

    {{-- Back + actions toolbar --}}
    <div class="flex items-center justify-between mb-5 gap-3 flex-wrap">
        <a href="{{ route('contensio.account.contact.messages.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('contensio::admin.contact.messages.back') }}
        </a>
        <div class="flex items-center gap-2">
            @if($message->status !== 'replied')
            <button type="button"
                    @click="document.getElementById('status-action-value').value='mark_replied'; document.getElementById('status-form').submit()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                <i class="bi bi-check2-circle"></i>
                {{ __('contensio::admin.contact.messages.mark_replied') }}
            </button>
            @endif
            @if($message->status === 'spam')
            <button type="button"
                    @click="document.getElementById('status-action-value').value='mark_read'; document.getElementById('status-form').submit()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">
                <i class="bi bi-slash-circle"></i>
                {{ __('contensio::admin.contact.messages.not_spam') }}
            </button>
            @else
            <button type="button"
                    @click="document.getElementById('status-action-value').value='mark_spam'; document.getElementById('status-form').submit()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-amber-700 border border-amber-300 hover:bg-amber-50 rounded-lg transition-colors">
                <i class="bi bi-slash-circle"></i>
                {{ __('contensio::admin.contact.messages.mark_spam') }}
            </button>
            @endif
            <button type="button"
                    @click="$dispatch('cms:confirm', {
                        title: '{{ __('contensio::admin.contact.messages.confirm_delete_one') }}',
                        description: '{{ __('contensio::admin.contact.messages.confirm_delete_one_desc') }}',
                        confirmLabel: '{{ __('contensio::admin.contact.messages.delete') }}',
                        formId: 'delete-form'
                    })"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-red-600 border border-red-200 hover:bg-red-50 rounded-lg transition-colors">
                <i class="bi bi-trash"></i>
                {{ __('contensio::admin.contact.messages.delete') }}
            </button>
        </div>
    </div>

    {{-- Message card --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <h1 class="text-lg font-bold text-gray-900">
                        @if($message->subject)
                            {{ $message->subject }}
                        @else
                            {{ __('contensio::admin.contact.messages.no_subject') }}
                        @endif
                    </h1>
                    <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                        <span class="text-sm text-gray-700 font-medium">{{ $message->name }}</span>
                        <a href="mailto:{{ $message->email }}"
                           class="text-sm text-ember-600 hover:text-ember-700 transition-colors">{{ $message->email }}</a>
                        <span class="text-xs text-gray-400">
                            {{ $message->created_at->format('M j, Y \a\t g:i A') }}
                            &nbsp;·&nbsp;
                            {{ $message->created_at->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Labels row --}}
                    @if($allLabels->isNotEmpty())
                    <div class="flex items-center gap-1.5 mt-3 flex-wrap">
                        {{-- Assigned label chips --}}
                        <template x-for="lbl in labels" :key="lbl.id">
                            <span class="inline-flex items-center gap-1 pl-2 pr-1 py-0.5 rounded-full text-xs font-medium text-white"
                                  :style="'background-color:' + lbl.color">
                                <span x-text="lbl.name"></span>
                                <button type="button" @click="removeLabel(lbl.id)"
                                        class="ml-0.5 rounded-full hover:bg-black/20 p-0.5 transition-colors leading-none"
                                        :disabled="loading">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </span>
                        </template>

                        {{-- Add label button + dropdown --}}
                        <div class="relative">
                            <button type="button"
                                    @click="showPicker = !showPicker"
                                    x-show="availableLabels.length > 0"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-gray-400 border border-dashed border-gray-300 hover:border-gray-400 hover:text-gray-600 transition-colors">
                                <i class="bi bi-plus text-sm leading-none"></i>
                                {{ __('contensio::admin.contact.messages.add_label') }}
                            </button>
                            <div x-show="showPicker" x-cloak @click.outside="showPicker = false"
                                 class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 min-w-[160px] py-1">
                                <template x-for="lbl in availableLabels" :key="lbl.id">
                                    <button type="button" @click="addLabel(lbl)"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors text-left">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                              :style="'background-color:' + lbl.color"></span>
                                        <span x-text="lbl.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
                @php
                    $statusColors = [
                        'new'     => 'bg-blue-100 text-blue-700',
                        'read'    => 'bg-gray-100 text-gray-600',
                        'replied' => 'bg-green-100 text-green-700',
                        'spam'    => 'bg-red-100 text-red-600',
                    ];
                @endphp
                <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$message->status] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($message->status) }}
                </span>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5">
            <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $message->message }}</p>
        </div>

        {{-- Extra fields --}}
        @if($message->extra_data && count($message->extra_data))
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
                {{ __('contensio::admin.contact.messages.extra_fields') }}
            </p>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($message->extra_data as $key => $value)
                <div>
                    <dt class="text-xs font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                    <dd class="text-sm text-gray-800 mt-0.5">
                        @if(is_array($value))
                            {{ implode(', ', $value) }}
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                </div>
                @endforeach
            </dl>
        </div>
        @endif

        {{-- Attachments --}}
        @if($message->files->isNotEmpty())
        <div class="px-6 py-4 border-t border-gray-100">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
                {{ __('contensio::admin.contact.messages.attachments') }}
                <span class="normal-case font-normal ml-1">({{ $message->files->count() }})</span>
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach($message->files as $file)
                <a href="{{ $file->url() }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors max-w-xs truncate">
                    @php
                        $ext = pathinfo($file->file_name, PATHINFO_EXTENSION);
                        $iconMap = [
                            'pdf'  => 'bi-file-earmark-pdf text-red-500',
                            'jpg'  => 'bi-file-earmark-image text-blue-500',
                            'jpeg' => 'bi-file-earmark-image text-blue-500',
                            'png'  => 'bi-file-earmark-image text-blue-500',
                            'gif'  => 'bi-file-earmark-image text-blue-500',
                            'doc'  => 'bi-file-earmark-word text-blue-700',
                            'docx' => 'bi-file-earmark-word text-blue-700',
                            'xls'  => 'bi-file-earmark-excel text-green-700',
                            'xlsx' => 'bi-file-earmark-excel text-green-700',
                            'zip'  => 'bi-file-earmark-zip text-amber-600',
                            'rar'  => 'bi-file-earmark-zip text-amber-600',
                        ];
                        $iconClass = $iconMap[strtolower($ext)] ?? 'bi-file-earmark text-gray-400';
                    @endphp
                    <i class="bi {{ $iconClass }} text-base shrink-0"></i>
                    <span class="truncate">{{ $file->file_name }}</span>
                    <span class="text-xs text-gray-400 shrink-0">{{ $file->humanSize() }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Meta --}}
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 flex items-center gap-6 flex-wrap">
            @if($message->ip_address)
            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                <i class="bi bi-globe2"></i>
                <span>{{ $message->ip_address }}</span>
            </div>
            @endif
            @if($message->read_at)
            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                <i class="bi bi-eye"></i>
                <span>{{ __('contensio::admin.contact.messages.read_at') }} {{ $message->read_at->format('M j, Y g:i A') }}</span>
            </div>
            @endif
        </div>

    </div>

    {{-- Quick reply button (mailto link) --}}
    <div class="mt-4">
        <a href="mailto:{{ $message->email }}?subject=Re: {{ rawurlencode($message->subject ?? 'Your message') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-ember-500 hover:bg-ember-600 rounded-lg transition-colors">
            <i class="bi bi-reply"></i>
            {{ __('contensio::admin.contact.messages.reply_via_email') }}
        </a>
    </div>

</div>

@endsection
