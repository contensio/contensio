{{--
 | Fields builder — renders inside the Field Group edit page.
 | List of fields + add/edit modal (Alpine-driven).
 |
 | Expects: $group (loaded with ->fields and each field's ->translations)
--}}

@php
    // Gather current translation label/help/placeholder for each field (default lang)
    $defaultLangId = \Contensio\Models\Language::where('is_default', true)->value('id')
        ?? \Contensio\Models\Language::orderBy('id')->value('id');

    $fieldData = $group->fields->map(function ($f) use ($defaultLangId) {
        $t = $f->translations->firstWhere('language_id', $defaultLangId) ?? $f->translations->first();
        return [
            'id'              => $f->id,
            'key'             => $f->key,
            'type'            => $f->type,
            'section'         => $f->section,
            'is_translatable' => (bool) $f->is_translatable,
            'is_required'     => (bool) $f->is_required,
            'config'          => $f->config ?? [],
            'label'           => $t->label ?? $f->key,
            'placeholder'     => $t->placeholder ?? '',
            'help_text'       => $t->help_text ?? '',
            'position'        => $f->position,
        ];
    })->keyBy('id');
@endphp

<div x-data="fieldsBuilder(@js($fieldData->values()->all()))" class="bg-white rounded-xl border border-gray-200 overflow-hidden">

    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div>
            <h2 class="text-base font-semibold text-gray-900">Fields</h2>
            <p class="text-xs text-gray-500 mt-0.5">Add and organize fields that belong to this group.</p>
        </div>
        <button type="button" @click="openNew()"
                class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
            <i class="bi bi-plus-lg"></i> Add field
        </button>
    </div>

    {{-- Empty state --}}
    <template x-if="fields.length === 0">
        <div class="p-10 text-center">
            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i class="bi bi-ui-checks-grid text-gray-400 text-xl"></i>
            </div>
            <p class="text-sm text-gray-500">No fields yet. Add your first one.</p>
        </div>
    </template>

    {{-- Fields list --}}
    <template x-if="fields.length > 0">
        <div class="divide-y divide-gray-100">
            <template x-for="(f, i) in fields" :key="f.id">
                <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50">
                    <span class="shrink-0 text-gray-400 cursor-grab" title="Drag to reorder (coming soon)">
                        <i class="bi bi-grip-vertical"></i>
                    </span>
                    <div class="flex-1 min-w-0 grid grid-cols-12 gap-4 items-center">
                        <div class="col-span-5 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="f.label"></p>
                            <p class="text-xs text-gray-500 font-mono truncate" x-text="f.key"></p>
                        </div>
                        <div class="col-span-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700" x-text="f.type"></span>
                        </div>
                        <div class="col-span-3 text-xs text-gray-500 truncate">
                            <span x-show="f.section" x-text="f.section"></span>
                            <span x-show="f.is_required" class="ml-2 text-red-600">required</span>
                            <span x-show="f.is_translatable" class="ml-2 text-blue-600">i18n</span>
                        </div>
                        <div class="col-span-1 text-right flex items-center justify-end gap-3">
                            <button type="button" @click="openEdit(f)" class="text-gray-500 hover:text-blue-600" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" @click="confirmDelete(f)" class="text-gray-500 hover:text-red-600" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- Modal: create / edit field --}}
    <div x-show="modalOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeModal()"></div>

        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             class="relative bg-white rounded-2xl shadow-2xl w-full sm:max-w-2xl mx-auto overflow-hidden max-h-[90vh] flex flex-col">

            <form :action="editing.id ? `{{ url('/') }}/{{ config('contensio.route_prefix') }}/fields/${editing.id}` : `{{ route('contensio.account.fields.store', $group->id) }}`"
                  method="POST" class="flex flex-col min-h-0">
                @csrf
                <template x-if="editing.id">@method('PUT')</template>

                {{-- Modal header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <h3 class="text-base font-semibold text-gray-900" x-text="editing.id ? 'Edit field' : 'New field'"></h3>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-700">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                {{-- Modal body --}}
                <div class="px-6 py-5 space-y-4 overflow-y-auto">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                            <input type="text" name="label" x-model="editing.label" required
                                   placeholder="e.g. Price"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Key</label>
                            <input type="text" name="key" x-model="editing.key" required pattern="[a-z0-9_]+"
                                   placeholder="e.g. price"
                                   :disabled="!!editing.id"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-500">
                            <p class="mt-1 text-xs text-gray-500" x-show="!editing.id">Lowercase letters, numbers, underscores.</p>
                            <p class="mt-1 text-xs text-gray-500" x-show="!!editing.id">Key can't be changed after creation.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="type" x-model="editing.type" required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                                <template x-for="t in types" :key="t.value">
                                    <option :value="t.value" x-text="t.label"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Section <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="text" name="section" x-model="editing.section"
                                   placeholder="e.g. Display"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                            <p class="mt-1 text-xs text-gray-500">Groups related fields visually.</p>
                        </div>
                    </div>

                    {{-- Per-type config --}}
                    <div x-show="hasTypeConfig(editing.type)" class="p-4 bg-gray-50 rounded-lg border border-gray-200 space-y-3">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Type configuration</p>

                        {{-- text / textarea --}}
                        <template x-if="['text','textarea'].includes(editing.type)">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Max length</label>
                                    <input type="number" name="config[max_length]" x-model="editing.config.max_length" min="1"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                                </div>
                                <template x-if="editing.type === 'textarea'">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Rows</label>
                                        <input type="number" name="config[rows]" x-model="editing.config.rows" min="1" max="20"
                                               class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- number --}}
                        <template x-if="editing.type === 'number'">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Min</label>
                                    <input type="number" name="config[min]" x-model="editing.config.min" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Max</label>
                                    <input type="number" name="config[max]" x-model="editing.config.max" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Step</label>
                                    <input type="number" name="config[step]" x-model="editing.config.step" step="any" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Suffix</label>
                                    <input type="text" name="config[suffix]" x-model="editing.config.suffix" placeholder="€, kg" class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                </div>
                            </div>
                        </template>

                        {{-- date --}}
                        <template x-if="editing.type === 'date'">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="config[with_time]" value="0">
                                <input type="checkbox" name="config[with_time]" value="1" x-model="editing.config.with_time"
                                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
                                <span class="text-sm text-gray-700">Include time</span>
                            </label>
                        </template>

                        {{-- select / multi-select --}}
                        <template x-if="['select','multi-select'].includes(editing.type)">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Options <span class="text-gray-400 font-normal">(one per line — <code>value:label</code>)</span></label>
                                <textarea name="config[options]" x-model="editing.config.optionsText" rows="5"
                                          placeholder="red:Red&#10;blue:Blue&#10;green:Green"
                                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ember-500"></textarea>
                            </div>
                        </template>

                        {{-- media --}}
                        <template x-if="editing.type === 'media'">
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="config[multiple]" value="0">
                                    <input type="checkbox" name="config[multiple]" value="1" x-model="editing.config.multiple"
                                           class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
                                    <span class="text-sm text-gray-700">Allow multiple files</span>
                                </label>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Accepted types <span class="text-gray-400 font-normal">(MIME filter, e.g. <code>image/*</code>)</span></label>
                                    <input type="text" name="config[accept]" x-model="editing.config.accept" placeholder="image/*"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Placeholder <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="text" name="placeholder" x-model="editing.placeholder"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Help text <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="text" name="help_text" x-model="editing.help_text"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-6 pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_required" value="0">
                            <input type="checkbox" name="is_required" value="1" x-model="editing.is_required"
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
                            <span class="text-sm text-gray-700">Required</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_translatable" value="0">
                            <input type="checkbox" name="is_translatable" value="1" x-model="editing.is_translatable"
                                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
                            <span class="text-sm text-gray-700">Translatable <span class="text-xs text-gray-500">(per-language values)</span></span>
                        </label>
                    </div>

                </div>

                {{-- Modal footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100 shrink-0">
                    <button type="button" @click="closeModal()" class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2">Cancel</button>
                    <button type="submit" class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors"
                            x-text="editing.id ? 'Save field' : 'Add field'"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete forms (rendered once per field, submitted on confirm) --}}
    @foreach($group->fields as $f)
    <form id="delete-field-{{ $f->id }}" method="POST" action="{{ route('contensio.account.fields.destroy', $f->id) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
    @endforeach
</div>

<script>
function fieldsBuilder(initialFields) {
    return {
        fields: initialFields.map(f => ({
            ...f,
            config: f.config || {},
            // If options is an array, flatten it into the textarea form "value:label\n..."
            ...(f.config && Array.isArray(f.config.options) ? {
                config: {
                    ...f.config,
                    optionsText: f.config.options.map(o => `${o.value}:${o.label}`).join('\n'),
                }
            } : {}),
        })),
        modalOpen: false,
        editing: {},
        init() { this.editing = this.blank(); },
        types: [
            { value: 'text',         label: 'Text (single line)' },
            { value: 'textarea',     label: 'Textarea (multi-line)' },
            { value: 'rich-text',    label: 'Rich text' },
            { value: 'number',       label: 'Number' },
            { value: 'boolean',      label: 'Boolean (toggle)' },
            { value: 'date',         label: 'Date' },
            { value: 'select',       label: 'Select (single)' },
            { value: 'multi-select', label: 'Multi-select' },
            { value: 'media',        label: 'Media (file/image)' },
            { value: 'url',          label: 'URL / Link' },
        ],
        blank() {
            return {
                id: null, key: '', type: 'text', label: '', section: '',
                is_translatable: false, is_required: false,
                placeholder: '', help_text: '',
                config: {},
            };
        },
        openNew() {
            this.editing = this.blank();
            this.modalOpen = true;
        },
        openEdit(f) {
            this.editing = JSON.parse(JSON.stringify(f));
            this.editing.config = this.editing.config || {};
            if (Array.isArray(this.editing.config.options)) {
                this.editing.config.optionsText = this.editing.config.options.map(o => `${o.value}:${o.label}`).join('\n');
            }
            this.modalOpen = true;
        },
        closeModal() { this.modalOpen = false; },
        hasTypeConfig(type) {
            return ['text','textarea','number','date','select','multi-select','media'].includes(type);
        },
        confirmDelete(f) {
            window.dispatchEvent(new CustomEvent('cms:confirm', { detail: {
                title: 'Delete field?',
                description: `"${f.label}" will be removed. Stored values for this field will be orphaned.`,
                confirmLabel: 'Delete',
                formId: `delete-field-${f.id}`,
            }}));
        },
    };
}
</script>
