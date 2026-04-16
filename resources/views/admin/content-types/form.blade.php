{{--
 | Contensio - The open content platform for Laravel.
 | Admin — content-types form.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', $type ? 'Edit Content Type' : 'New Content Type')

@section('breadcrumb')
    <a href="{{ route('contensio.account.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('contensio.account.content-types.index') }}" class="text-gray-500 hover:text-gray-700">Content Types</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">{{ $type ? 'Edit' : 'New Content Type' }}</span>
@endsection

@section('content')

@php
    $defaultLangId = $defaultLanguage?->id ?? $languages->first()?->id ?? 1;
    $multiLang     = $languages->count() > 1;
@endphp

<div class="max-w-2xl">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $type ? 'Edit Content Type' : 'New Content Type' }}</h1>
            <p class="text-sm text-gray-400 mt-0.5">
                {{ $type ? 'Update labels, slug and feature options.' : 'Define a new custom post type.' }}
            </p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-md px-4 py-3">
        <ul class="text-sm text-red-700 space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $type ? route('contensio.account.content-types.update', $type->id) : route('contensio.account.content-types.store') }}">
        @csrf
        @if($type) @method('PUT') @endif

        {{-- Labels (per language) --}}
        <div class="bg-white border border-gray-200 rounded-md overflow-hidden mb-4"
             x-data="{ activeLang: {{ $defaultLangId }} }">

            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                <h2 class="text-base font-bold text-gray-800 flex-1">Labels</h2>

                @if($multiLang)
                <div class="flex items-center gap-1">
                    @foreach($languages as $lang)
                    <button type="button"
                            @click="activeLang = {{ $lang->id }}"
                            :class="activeLang === {{ $lang->id }}
                                ? 'bg-slate-800 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-bold transition-colors">
                        {{ strtoupper($lang->code) }}
                        @if($lang->is_default)<span class="opacity-50">★</span>@endif
                        @if($lang->status === 'inactive')<span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block" title="Admin only"></span>@endif
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="px-5 py-5">
                @foreach($languages as $lang)
                @php
                    $defSingular = old("translations.{$lang->id}.singular", $existing[$lang->id]['singular'] ?? '');
                    $defPlural   = old("translations.{$lang->id}.plural",   $existing[$lang->id]['plural']   ?? '');
                    $defSlug     = old("translations.{$lang->id}.slug",     $existing[$lang->id]['slug']     ?? '');
                    $slugTouched = ($defSlug !== '') ? 'true' : 'false';
                    $isDefault   = $lang->id === $defaultLangId;
                @endphp
                <div x-show="activeLang === {{ $lang->id }}" x-cloak="{{ $multiLang ? '' : 'false' }}">
                @php
                    $exCreate   = old("translations.{$lang->id}.labels.create",    $existing[$lang->id]['create']    ?? '');
                    $exEdit     = old("translations.{$lang->id}.labels.edit",      $existing[$lang->id]['edit']      ?? '');
                    $exDelete   = old("translations.{$lang->id}.labels.delete",    $existing[$lang->id]['delete']    ?? '');
                    $exAll      = old("translations.{$lang->id}.labels.all",       $existing[$lang->id]['all']       ?? '');
                    $exSearch   = old("translations.{$lang->id}.labels.search",    $existing[$lang->id]['search']    ?? '');
                    $exNotFound = old("translations.{$lang->id}.labels.not_found", $existing[$lang->id]['not_found'] ?? '');
                @endphp
                    <div x-data="{
                            singular: @js($defSingular),
                            plural:   @js($defPlural),
                            slug: @js($defSlug),
                            slugTouched: {{ $slugTouched }},
                            showMore: {{ ($exCreate || $exEdit || $exAll || $exNotFound) ? 'true' : 'false' }},
                            lbl: {
                                create:    @js($exCreate),
                                edit:      @js($exEdit),
                                delete:    @js($exDelete),
                                all:       @js($exAll),
                                search:    @js($exSearch),
                                not_found: @js($exNotFound),
                            },
                            lblTouched: {
                                create:    {{ $exCreate   !== '' ? 'true' : 'false' }},
                                edit:      {{ $exEdit     !== '' ? 'true' : 'false' }},
                                delete:    {{ $exDelete   !== '' ? 'true' : 'false' }},
                                all:       {{ $exAll      !== '' ? 'true' : 'false' }},
                                search:    {{ $exSearch   !== '' ? 'true' : 'false' }},
                                not_found: {{ $exNotFound !== '' ? 'true' : 'false' }},
                            },
                            generate() {
                                if (!this.slugTouched) {
                                    this.slug = this.singular.toLowerCase()
                                        .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
                                }
                                if (!this.lblTouched.create)    this.lbl.create    = 'Add New ' + this.singular;
                                if (!this.lblTouched.edit)      this.lbl.edit      = 'Edit ' + this.singular;
                                if (!this.lblTouched.delete)    this.lbl.delete    = 'Delete ' + this.singular;
                                if (!this.lblTouched.all)       this.lbl.all       = 'All ' + this.plural;
                                if (!this.lblTouched.search)    this.lbl.search    = 'Search ' + this.plural;
                                if (!this.lblTouched.not_found) this.lbl.not_found = 'No ' + this.plural + ' found';
                            }
                         }"
                         x-init="generate()"
                         class="space-y-4">

                        {{-- Singular + Plural --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Singular name @if($isDefault)<span class="text-red-500">*</span>@endif
                                </label>
                                <input type="text"
                                       name="translations[{{ $lang->id }}][singular]"
                                       x-model="singular"
                                       @input="generate()"
                                       placeholder="e.g. Portfolio Item"
                                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent"
                                       {{ $isDefault ? 'required' : '' }}>
                                @error("translations.{$lang->id}.singular")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Plural name @if($isDefault)<span class="text-red-500">*</span>@endif
                                </label>
                                <input type="text"
                                       name="translations[{{ $lang->id }}][plural]"
                                       x-model="plural"
                                       @input="generate()"
                                       placeholder="e.g. Portfolio Items"
                                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent"
                                       {{ $isDefault ? 'required' : '' }}>
                                @error("translations.{$lang->id}.plural")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Slug --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                URL slug @if($isDefault)<span class="text-red-500">*</span>@endif
                            </label>
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-2 rounded-l border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm select-none">/</span>
                                <input type="text"
                                       name="translations[{{ $lang->id }}][slug]"
                                       x-model="slug"
                                       @input="slugTouched = true"
                                       placeholder="portfolio-items"
                                       class="flex-1 rounded-r border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent"
                                       {{ $isDefault ? 'required' : '' }}>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Lowercase letters, numbers and hyphens only.</p>
                            @error("translations.{$lang->id}.slug")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- More labels toggle --}}
                        <div>
                            <button type="button"
                                    @click="showMore = !showMore"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-400 hover:text-gray-700 transition-colors">
                                <svg class="w-3.5 h-3.5 transition-transform" :class="showMore ? 'rotate-90' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span x-text="showMore ? 'Hide extra labels' : 'More labels…'"></span>
                            </button>

                            <div x-show="showMore" x-cloak class="mt-3 grid grid-cols-2 gap-3 p-4 bg-gray-50 rounded border border-gray-200">
                                <p class="col-span-2 text-xs text-gray-400 -mt-1 mb-1">These labels are auto-generated. Override only if needed.</p>

                                @php
                                    $extraLabels = [
                                        'create'    => 'Add new (button)',
                                        'edit'      => 'Edit (button)',
                                        'delete'    => 'Delete (button)',
                                        'all'       => 'All items (heading)',
                                        'search'    => 'Search placeholder',
                                        'not_found' => 'Empty state text',
                                    ];
                                @endphp

                                @foreach($extraLabels as $key => $labelName)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ $labelName }}</label>
                                    <input type="text"
                                           name="translations[{{ $lang->id }}][labels][{{ $key }}]"
                                           x-model="lbl.{{ $key }}"
                                           @input="lblTouched.{{ $key }} = true"
                                           class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                                </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
                @endforeach

                {{-- Icon (shared — not language-specific) --}}
                @php $defIcon = old('icon', $type?->icon ?? ''); @endphp
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Icon <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent w-64">
                            <button type="button"
                                    class="icon-picker-preview flex items-center justify-center w-10 shrink-0 bg-gray-50 border-r border-gray-300 text-gray-500 hover:bg-gray-100 transition-colors cursor-pointer"
                                    title="Choose icon">
                                <i class="bi {{ $defIcon ?: 'bi-grid-3x3-gap' }}" style="{{ $defIcon ? '' : 'opacity:0.35' }}"></i>
                            </button>
                            <input type="text"
                                   name="icon"
                                   value="{{ $defIcon }}"
                                   placeholder="Click to choose…"
                                   data-icon-picker
                                   readonly
                                   class="flex-1 px-3 py-2 text-sm bg-white cursor-pointer focus:outline-none">
                        </div>
                        @if($defIcon)
                        <button type="button"
                                onclick="this.previousElementSibling.querySelector('[data-icon-picker]').value=''; this.previousElementSibling.querySelector('.icon-picker-preview i').className=''; this.style.display='none';"
                                class="text-xs text-gray-400 hover:text-red-500 transition-colors">
                            Clear
                        </button>
                        @endif
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">Click the field to browse Bootstrap Icons.</p>
                </div>
            </div>
        </div>

        {{-- Features (shared — not language-specific) --}}
        <div class="bg-white border border-gray-200 rounded-md overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-800">Features</h2>
            </div>
            <div class="px-5 py-5 grid grid-cols-1 sm:grid-cols-2 gap-3">

                @php
                    $features = [
                        'has_slug'           => ['label' => 'URL slug',         'desc' => 'Each item gets a unique URL slug',       'default' => true],
                        'has_excerpt'        => ['label' => 'Excerpt',          'desc' => 'Short summary field'],
                        'has_featured_image' => ['label' => 'Featured image',   'desc' => 'Thumbnail / cover image'],
                        'has_categories'     => ['label' => 'Categories',       'desc' => 'Assign categories to items'],
                        'has_tags'           => ['label' => 'Tags',             'desc' => 'Assign tags to items'],
                        'has_comments'       => ['label' => 'Comments',         'desc' => 'Enable comment threads'],
                        'has_seo'            => ['label' => 'SEO fields',       'desc' => 'Meta title, description, og:image',      'default' => true],
                        'is_hierarchical'    => ['label' => 'Hierarchical',     'desc' => 'Items can have parent/child nesting'],
                    ];
                @endphp

                @foreach($features as $key => $feature)
                @php $checked = old($key, $type ? $type->$key : ($feature['default'] ?? false)); @endphp
                <label class="flex items-start gap-3 p-3 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                    <input type="checkbox"
                           name="{{ $key }}"
                           value="1"
                           {{ $checked ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
                    <div>
                        <p class="text-sm font-medium text-gray-700">{{ $feature['label'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $feature['desc'] }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- ── Custom Fields (attached field groups) ──────────────────── --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6"
             x-data="cfAttach(@js($allFieldGroups->map(fn($g)=>['id'=>$g->id,'label'=>$g->label,'key'=>$g->key])->all()), @js($attachedGroupIds))">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Custom fields</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Attach field groups. Their fields will appear on this content type's edit form.</p>
                </div>
                @if(empty($allFieldGroups ?? []))
                <a href="{{ route('contensio.account.field-groups.create') }}"
                   class="text-sm font-medium text-ember-600 hover:text-ember-700">Create a field group →</a>
                @endif
            </div>

            {{-- Hidden inputs — rebuilt from Alpine state in submission order --}}
            <template x-for="id in attached" :key="id">
                <input type="hidden" name="field_group_ids[]" :value="id">
            </template>

            {{-- Attached chips --}}
            <template x-if="attached.length > 0">
                <ul class="space-y-2 mb-3">
                    <template x-for="(id, i) in attached" :key="id">
                        <li class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5">
                            <span class="text-gray-400 font-mono text-xs" x-text="i + 1 + '.'"></span>
                            <span class="flex-1 text-sm font-medium text-gray-900" x-text="labelFor(id)"></span>
                            <span class="text-xs text-gray-400 font-mono" x-text="keyFor(id)"></span>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="moveUp(i)" :disabled="i === 0"
                                        class="w-7 h-7 rounded hover:bg-gray-200 text-gray-500 disabled:opacity-30 disabled:hover:bg-transparent" title="Move up">
                                    <i class="bi bi-chevron-up"></i>
                                </button>
                                <button type="button" @click="moveDown(i)" :disabled="i === attached.length - 1"
                                        class="w-7 h-7 rounded hover:bg-gray-200 text-gray-500 disabled:opacity-30 disabled:hover:bg-transparent" title="Move down">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <button type="button" @click="detach(id)"
                                        class="w-7 h-7 rounded hover:bg-red-50 text-gray-500 hover:text-red-600" title="Detach">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </li>
                    </template>
                </ul>
            </template>
            <template x-if="attached.length === 0">
                <p class="text-sm text-gray-500 italic mb-3">No field groups attached yet.</p>
            </template>

            {{-- Attach picker --}}
            <div x-show="available.length > 0" class="flex items-center gap-2">
                <select x-ref="picker" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
                    <template x-for="g in available" :key="g.id">
                        <option :value="g.id" x-text="g.label + ' (' + g.key + ')'"></option>
                    </template>
                </select>
                <button type="button" @click="attach($refs.picker.value)"
                        class="bg-ember-500 hover:bg-ember-600 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
                    Attach
                </button>
            </div>
            <p x-show="available.length === 0 && all.length > 0" class="text-xs text-gray-400 italic">All field groups are already attached.</p>
        </div>

        <script>
            function cfAttach(all, initial) {
                return {
                    all: all,
                    attached: initial.map(v => parseInt(v)).filter(Boolean),
                    get available() {
                        return this.all.filter(g => !this.attached.includes(parseInt(g.id)));
                    },
                    labelFor(id) { const g = this.all.find(g => g.id == id); return g ? g.label : '?'; },
                    keyFor(id)   { const g = this.all.find(g => g.id == id); return g ? g.key   : ''; },
                    attach(id) {
                        id = parseInt(id);
                        if (id && ! this.attached.includes(id)) this.attached.push(id);
                    },
                    detach(id) { this.attached = this.attached.filter(x => x !== id); },
                    moveUp(i)  { if (i > 0) [this.attached[i-1], this.attached[i]] = [this.attached[i], this.attached[i-1]]; },
                    moveDown(i){ if (i < this.attached.length - 1) [this.attached[i+1], this.attached[i]] = [this.attached[i], this.attached[i+1]]; },
                };
            }
        </script>

        {{-- ── Image Sizes ─────────────────────────────────────────────────── --}}
        <div class="bg-white border border-gray-200 rounded-md overflow-hidden mb-6"
             x-data="imageSizes(@js($imageSizes), @js($defaultImageSizes))">

            <div class="px-5 py-4 border-b border-gray-100 flex items-start gap-3">
                <div class="flex-1">
                    <h2 class="text-base font-bold text-gray-800">Image Sizes</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Choose which variants are generated when an image is uploaded. Sizes apply globally — each active size is generated for every uploaded image.</p>
                </div>
                <button type="button"
                        @click="resetToDefaults()"
                        class="shrink-0 text-xs font-medium text-gray-400 hover:text-gray-700 border border-gray-200 hover:border-gray-300 rounded px-2.5 py-1.5 transition-colors mt-0.5">
                    Reset to defaults
                </button>
            </div>

            <div class="divide-y divide-gray-100">
                <template x-for="(size, index) in sizes" :key="size.key">
                    <div class="px-5 py-4">

                        {{-- Row header: toggle + label --}}
                        <div class="flex items-center gap-3 mb-0" :class="size.active ? 'mb-3' : ''">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox"
                                       :name="'image_sizes[' + size.key + '][active]'"
                                       value="1"
                                       x-model="size.active"
                                       class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-ember-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-ember-500"></div>
                            </label>
                            <span class="text-sm font-semibold text-gray-800" x-text="size.label"></span>
                            <span class="text-xs text-gray-400 font-mono" x-text="size.key"></span>
                            <span class="ml-auto text-xs text-gray-400"
                                  x-text="size.width + ' × ' + size.height + ' px  ·  ' + fitLabel(size.fit)"></span>
                        </div>

                        {{-- Expanded config (visible when active) --}}
                        <div x-show="size.active" x-cloak class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">

                            {{-- Hidden inputs always submitted for active sizes --}}
                            <input type="hidden" :name="'image_sizes[' + size.key + '][label]'"      :value="size.label">
                            <input type="hidden" :name="'image_sizes[' + size.key + '][quality]'"    :value="size.quality">

                            {{-- Width --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Width (px)</label>
                                <input type="number" min="1" max="9999"
                                       :name="'image_sizes[' + size.key + '][width]'"
                                       x-model.number="size.width"
                                       class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                            </div>

                            {{-- Height --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Height (px)</label>
                                <input type="number" min="1" max="9999"
                                       :name="'image_sizes[' + size.key + '][height]'"
                                       x-model.number="size.height"
                                       class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                            </div>

                            {{-- Fit --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Fit method</label>
                                <select :name="'image_sizes[' + size.key + '][fit]'"
                                        x-model="size.fit"
                                        class="w-full rounded border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                                    @foreach($fitOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Background color (only for pad) --}}
                            <div x-show="size.fit === 'pad'">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Fill color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color"
                                           :name="'image_sizes[' + size.key + '][background]'"
                                           x-model="size.background"
                                           class="w-10 h-8 rounded border border-gray-300 cursor-pointer p-0.5">
                                    <span class="text-xs font-mono text-gray-500" x-text="size.background"></span>
                                </div>
                            </div>
                            {{-- Placeholder to keep grid consistent when pad is not selected --}}
                            <div x-show="size.fit !== 'pad'"></div>

                        </div>
                    </div>
                </template>
            </div>
        </div>

        <script>
            function imageSizes(initial, defaults) {
                const fitLabels = @json($fitOptions);
                return {
                    sizes: initial,
                    fitLabel(key) { return fitLabels[key] || key; },
                    resetToDefaults() {
                        this.sizes = JSON.parse(JSON.stringify(defaults));
                    },
                };
            }
        </script>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-md transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $type ? 'Save Changes' : 'Create Content Type' }}
            </button>
            <a href="{{ route('contensio.account.content-types.index') }}"
               class="text-sm font-medium text-gray-500 hover:text-gray-700 px-4 py-2.5 rounded-md hover:bg-gray-100 transition-colors">
                Cancel
            </a>
        </div>

    </form>
</div>

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@endsection
