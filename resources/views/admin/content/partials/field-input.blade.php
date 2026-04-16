{{--
 | Custom field input renderer — one partial, switches on $field->type.
 |
 | Expects:
 |   $field      — Field model
 |   $inputName  — e.g. "fields[12]" or "fields[12][3]" (with language_id)
 |   $label      — rendered label
 |   $help       — optional help text
 |   $placeholder— optional placeholder
 |   $cfg        — array, field.config
 |   $current    — current saved value (string or JSON)
--}}

@php
    $fieldId = 'cf-' . $field->id . '-' . str_replace(['[',']'], ['-',''], $inputName);
    $isRequired = (bool) $field->is_required;

    // Decode JSON for multi-value types
    $decodedCurrent = $current;
    if (in_array($field->type, ['multi-select'], true) && is_string($current) && $current !== '') {
        $decoded = json_decode($current, true);
        $decodedCurrent = is_array($decoded) ? $decoded : [];
    }
@endphp

<div class="space-y-1.5">
    <label for="{{ $fieldId }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($isRequired)<span class="text-red-500">*</span>@endif
    </label>

    @switch($field->type)

    @case('text')
        <input type="text" id="{{ $fieldId }}" name="{{ $inputName }}"
               value="{{ is_array($current) ? '' : $current }}"
               @if(! empty($cfg['max_length'])) maxlength="{{ (int) $cfg['max_length'] }}" @endif
               @if($isRequired) required @endif
               placeholder="{{ $placeholder }}"
               class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
        @break

    @case('textarea')
        <textarea id="{{ $fieldId }}" name="{{ $inputName }}"
                  rows="{{ $cfg['rows'] ?? 4 }}"
                  @if(! empty($cfg['max_length'])) maxlength="{{ (int) $cfg['max_length'] }}" @endif
                  @if($isRequired) required @endif
                  placeholder="{{ $placeholder }}"
                  class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent resize-y">{{ is_array($current) ? '' : $current }}</textarea>
        @break

    @case('rich-text')
        {{-- Tiptap-backed. admin/partials/rich-text-editor.blade.php defines
             window.initRTE(textareaEl) which replaces the textarea with the editor. --}}
        <textarea id="{{ $fieldId }}" name="{{ $inputName }}" rows="6"
                  @if($isRequired) required @endif
                  x-data x-init="window.initRTE && window.initRTE($el)"
                  data-placeholder="{{ $placeholder ?: 'Start writing…' }}">{{ is_array($current) ? '' : $current }}</textarea>
        @break

    @case('number')
        <div class="flex items-center gap-2">
            <input type="number" id="{{ $fieldId }}" name="{{ $inputName }}"
                   value="{{ is_array($current) ? '' : $current }}"
                   @if(isset($cfg['min'])) min="{{ $cfg['min'] }}" @endif
                   @if(isset($cfg['max'])) max="{{ $cfg['max'] }}" @endif
                   @if(! empty($cfg['step'])) step="{{ $cfg['step'] }}" @endif
                   @if($isRequired) required @endif
                   placeholder="{{ $placeholder }}"
                   class="flex-1 rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
            @if(! empty($cfg['suffix']))
            <span class="text-sm text-gray-500 shrink-0">{{ $cfg['suffix'] }}</span>
            @endif
        </div>
        @break

    @case('boolean')
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="{{ $inputName }}" value="0">
            <input type="checkbox" id="{{ $fieldId }}" name="{{ $inputName }}" value="1"
                   {{ $current == '1' || $current === true ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
            <span class="text-sm text-gray-600">Yes</span>
        </label>
        @break

    @case('date')
        <input type="{{ ! empty($cfg['with_time']) ? 'datetime-local' : 'date' }}"
               id="{{ $fieldId }}" name="{{ $inputName }}"
               value="{{ is_array($current) ? '' : $current }}"
               @if($isRequired) required @endif
               class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
        @break

    @case('select')
        <select id="{{ $fieldId }}" name="{{ $inputName }}"
                @if($isRequired) required @endif
                class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
            <option value="">— Select —</option>
            @foreach((array) ($cfg['options'] ?? []) as $opt)
            <option value="{{ $opt['value'] ?? '' }}" {{ $current == ($opt['value'] ?? null) ? 'selected' : '' }}>
                {{ $opt['label'] ?? $opt['value'] ?? '' }}
            </option>
            @endforeach
        </select>
        @break

    @case('multi-select')
        @php $selected = is_array($decodedCurrent) ? $decodedCurrent : []; @endphp
        <select id="{{ $fieldId }}" name="{{ $inputName }}[]" multiple size="{{ min(8, count((array) ($cfg['options'] ?? []))) }}"
                @if($isRequired) required @endif
                class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
            @foreach((array) ($cfg['options'] ?? []) as $opt)
            <option value="{{ $opt['value'] ?? '' }}" {{ in_array($opt['value'] ?? null, $selected) ? 'selected' : '' }}>
                {{ $opt['label'] ?? $opt['value'] ?? '' }}
            </option>
            @endforeach
        </select>
        <p class="text-xs text-gray-400">Hold Ctrl / Cmd to select multiple.</p>
        @break

    @case('media')
        @php
            $multiple = (bool) ($cfg['multiple'] ?? false);
            $accept   = (string) ($cfg['accept']   ?? '');
            $stored   = is_array($current) ? $current : (is_string($current) && $current !== '' && str_starts_with($current, '[') ? (json_decode($current, true) ?: []) : ($current !== '' ? [$current] : []));
            $previewItems = [];
            if (! empty($stored)) {
                $ids = array_filter(array_map('intval', $stored));
                if ($ids) {
                    $previewItems = \Contensio\Models\Media::whereIn('id', $ids)->get();
                }
            }
        @endphp
        <input type="hidden" id="{{ $fieldId }}" name="{{ $inputName }}" value="{{ is_array($current) ? json_encode($current) : $current }}">
        <div class="space-y-2">
            <div data-media-preview="{{ $inputName }}" class="flex flex-wrap gap-2">
                @foreach($previewItems as $m)
                <div class="relative w-20 h-20 rounded-lg overflow-hidden border border-gray-200 bg-white flex items-center justify-center">
                    @if(str_starts_with((string) $m->mime_type, 'image/'))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk($m->disk)->url($m->file_path) }}" class="w-full h-full object-cover" alt="">
                    @else
                    <div class="text-[10px] p-1 font-mono break-all text-gray-500 text-center">{{ $m->file_name }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            <button type="button"
                    @click.prevent="$dispatch('cms:media-pick', { inputName: '{{ $inputName }}', multiple: {{ $multiple ? 'true' : 'false' }}, accept: '{{ $accept }}' })"
                    class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium text-sm px-4 py-2 rounded-lg transition-colors">
                <i class="bi bi-images"></i>
                @if($previewItems->isNotEmpty() ?? false)
                    Change
                @else
                    Select from library
                @endif
            </button>
        </div>
        @break

    @case('url')
        <input type="url" id="{{ $fieldId }}" name="{{ $inputName }}"
               value="{{ is_array($current) ? '' : $current }}"
               @if($isRequired) required @endif
               placeholder="{{ $placeholder ?: 'https://example.com' }}"
               class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
        @break

    @default
        <p class="text-sm text-red-600">Unknown field type: <code>{{ $field->type }}</code></p>
    @endswitch

    @if($help)
    <p class="text-xs text-gray-400">{{ $help }}</p>
    @endif
</div>
