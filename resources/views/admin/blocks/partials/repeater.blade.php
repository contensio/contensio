{{--
 | Contensio - The open content platform for Laravel.
 | Admin — blocks partials repeater.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@php
    $subFields  = $fieldDef['items'] ?? [];
    $addLabel   = $fieldDef['add_label'] ?? 'Add Item';
    $fullPrefix = "{$namePrefix}[{$fieldName}]";
@endphp

<div class="repeater-wrap" data-prefix="{{ $fullPrefix }}" data-fields="{{ json_encode($subFields) }}">

    <div class="repeater-items space-y-3">
        @forelse ($items as $idx => $item)
        <div class="repeater-item border border-gray-200 rounded-lg bg-gray-50">
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200">
                <span class="repeater-drag-handle cursor-grab text-gray-300 hover:text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                    </svg>
                </span>
                <span class="text-xs text-gray-500 font-medium">Item {{ $idx + 1 }}</span>
                <button type="button" class="repeater-remove text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4 space-y-3">
                @foreach ($subFields as $subName => $subDef)
                    @include('contensio::admin.blocks.partials.field', [
                        'fieldName'  => $subName,
                        'fieldDef'   => $subDef,
                        'namePrefix' => "{$fullPrefix}[{$idx}]",
                        'value'      => $item[$subName] ?? null,
                    ])
                @endforeach
            </div>
        </div>
        @empty
        {{-- Empty state; JS will add items --}}
        @endforelse
    </div>

    <button type="button" class="repeater-add mt-3 inline-flex items-center gap-1.5 text-sm text-ember-600 hover:text-ember-700 font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ $addLabel }}
    </button>

</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function initRepeaters() {
        document.querySelectorAll('.repeater-wrap').forEach(function (wrap) {
            const itemsEl  = wrap.querySelector('.repeater-items');
            const addBtn   = wrap.querySelector('.repeater-add');
            const prefix   = wrap.dataset.prefix;
            const fields   = JSON.parse(wrap.dataset.fields || '{}');

            // Remove button
            wrap.addEventListener('click', function (e) {
                const removeBtn = e.target.closest('.repeater-remove');
                if (!removeBtn) return;
                const item = removeBtn.closest('.repeater-item');
                item.remove();
                reindex(wrap, prefix);
            });

            // Add button
            if (addBtn) {
                addBtn.addEventListener('click', function () {
                    const idx  = wrap.querySelectorAll('.repeater-item').length;
                    const html = buildItem(fields, prefix, idx);
                    itemsEl.insertAdjacentHTML('beforeend', html);
                    reindex(wrap, prefix);
                });
            }
        });
    }

    function buildItem(fields, prefix, idx) {
        let inner = '';
        Object.entries(fields).forEach(([name, def]) => {
            const id   = `field_${prefix}_${idx}_${name}`.replace(/[\[\].]/g, '_');
            const type = def.type || 'text';
            const lbl  = def.label || name.replace(/_/g, ' ');
            const req  = def.required ? 'required' : '';
            const iName = `${prefix}[${idx}][${name}]`;

            inner += `<div class="block-field">`;
            inner += `<label for="${id}" class="block text-sm font-medium text-gray-700 mb-1.5">${lbl}</label>`;
            if (type === 'textarea') {
                inner += `<textarea id="${id}" name="${iName}" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500" ${req}></textarea>`;
            } else {
                inner += `<input type="${type === 'richtext' ? 'text' : type}" id="${id}" name="${iName}" value="" class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500" ${req}>`;
            }
            inner += `</div>`;
        });

        return `
        <div class="repeater-item border border-gray-200 rounded-lg bg-gray-50">
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200">
                <span class="repeater-drag-handle cursor-grab text-gray-300 hover:text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                    </svg>
                </span>
                <span class="text-xs text-gray-500 font-medium">Item ${idx + 1}</span>
                <button type="button" class="repeater-remove text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4 space-y-3">${inner}</div>
        </div>`;
    }

    function reindex(wrap, prefix) {
        wrap.querySelectorAll('.repeater-item').forEach(function (item, newIdx) {
            item.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.replace(/\[\d+\]/, `[${newIdx}]`);
            });
            item.querySelectorAll('[id]').forEach(function (el) {
                if (el.id) el.id = el.id.replace(/_\d+_/, `_${newIdx}_`);
            });
            const label = item.querySelector('.text-gray-500');
            if (label) label.textContent = `Item ${newIdx + 1}`;
        });
    }

    initRepeaters();
});
</script>
@endpush
@endonce
