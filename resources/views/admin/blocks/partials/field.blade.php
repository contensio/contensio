{{--
 | Contensio - The open content platform for Laravel.
 | Admin — blocks partials field.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@php
    $fieldType = $fieldDef['type'] ?? 'text';
    $inputName = "{$namePrefix}[{$fieldName}]";
    $inputId   = 'field_' . str_replace(['[', ']', '.'], '_', $inputName);
    $label     = $fieldDef['label'] ?? ucfirst(str_replace('_', ' ', $fieldName));
    $required  = $fieldDef['required'] ?? false;
    $help      = $fieldDef['help'] ?? null;
    $options   = $fieldDef['options'] ?? [];
    $default   = $fieldDef['default'] ?? null;
    $current   = old($inputName, $value ?? $default ?? '');
@endphp

<div class="block-field" data-field="{{ $fieldName }}">
    @switch($fieldType)

        @case('text')
        @case('url')
        @case('number')
        @case('email')
            <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                {{ $label }}@if($required)<span class="text-red-500 ml-0.5">*</span>@endif
            </label>
            <input type="{{ $fieldType }}" id="{{ $inputId }}" name="{{ $inputName }}"
                   value="{{ $current }}"
                   placeholder="{{ $fieldType === 'url' ? 'https://...' : '' }}"
                   class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent"
                   {{ $required ? 'required' : '' }}>
            @break

        @case('textarea')
            <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                {{ $label }}@if($required)<span class="text-red-500 ml-0.5">*</span>@endif
            </label>
            <textarea id="{{ $inputId }}" name="{{ $inputName }}" rows="4"
                      class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm resize-y
                             focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent"
                      {{ $required ? 'required' : '' }}>{{ $current }}</textarea>
            @break

        @case('richtext')
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                {{ $label }}@if($required)<span class="text-red-500 ml-0.5">*</span>@endif
            </label>
            <div class="block-richtext-container border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent">
                {{-- Toolbar --}}
                <div class="richtext-toolbar flex flex-wrap items-center gap-0.5 px-2 py-1.5 bg-gray-50 border-b border-gray-200">
                    <button data-cmd="formatBlock" data-arg="h2" title="Heading 2">H2</button>
                    <button data-cmd="formatBlock" data-arg="h3" title="Heading 3">H3</button>
                    <button data-cmd="formatBlock" data-arg="p"  title="Paragraph">P</button>
                    <span class="separator"></span>
                    <button data-cmd="bold"          title="Bold"><strong>B</strong></button>
                    <button data-cmd="italic"        title="Italic"><em>I</em></button>
                    <button data-cmd="underline"     title="Underline"><u>U</u></button>
                    <button data-cmd="strikeThrough" title="Strikethrough"><s>S</s></button>
                    <span class="separator"></span>
                    <button data-cmd="insertUnorderedList" title="Bullet list">• List</button>
                    <button data-cmd="insertOrderedList"   title="Numbered list">1. List</button>
                    <span class="separator"></span>
                    <button data-cmd="createLink" title="Link">Link</button>
                    <button data-cmd="unlink"     title="Remove link">Unlink</button>
                    <span class="separator"></span>
                    <button data-cmd="formatBlock" data-arg="blockquote" title="Blockquote">"</button>
                </div>
                {{-- Editor --}}
                <div class="block-richtext-editor" contenteditable="true" tabindex="0"></div>
                {{-- Hidden textarea synced to editor --}}
                <textarea name="{{ $inputName }}" id="{{ $inputId }}"
                          style="display:none" {{ $required ? 'required' : '' }}>{{ $current }}</textarea>
            </div>
            @break

        @case('select')
            <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                {{ $label }}
            </label>
            <select id="{{ $inputId }}" name="{{ $inputName }}"
                    class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm bg-white
                           focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                @foreach ($options as $optVal => $optLabel)
                    @php
                        $isAssoc = ! is_int($optVal);
                        $val     = $isAssoc ? $optVal : $optLabel;
                        $lbl     = $isAssoc ? $optLabel : $optLabel;
                    @endphp
                    <option value="{{ $val }}" {{ (string) $current === (string) $val ? 'selected' : '' }}>
                        {{ $lbl }}
                    </option>
                @endforeach
            </select>
            @break

        @case('boolean')
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="hidden"   name="{{ $inputName }}" value="0">
                <input type="checkbox" name="{{ $inputName }}" value="1" id="{{ $inputId }}"
                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500 cursor-pointer"
                       {{ $current ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">{{ $label }}</span>
            </label>
            @break

        @case('code')
            <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1.5">
                {{ $label }}@if($required)<span class="text-red-500 ml-0.5">*</span>@endif
            </label>
            <div class="code-editor-wrap">
                <textarea id="{{ $inputId }}" name="{{ $inputName }}"
                          rows="10" spellcheck="false"
                          {{ $required ? 'required' : '' }}>{{ $current }}</textarea>
            </div>
            @break

        @default
            <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
            <input type="text" id="{{ $inputId }}" name="{{ $inputName }}"
                   value="{{ $current }}"
                   class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">

    @endswitch

    @if ($help)
        <p class="mt-1 text-xs text-gray-400">{{ $help }}</p>
    @endif
</div>
