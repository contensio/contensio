{{--
 | Contensio - The open content platform for Laravel.
 | Admin — menus edit.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Edit Menu')

@section('breadcrumb')
<a href="{{ route('contensio.account.menus.index') }}" class="text-gray-400 hover:text-gray-700">Menus</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">
    @php
        $menuTrans = $menu->translations->firstWhere('language_id', $defaultLangId) ?? $menu->translations->first();
    @endphp
    {{ $menuTrans?->label ?? $menu->name }}
</span>
@endsection

@section('content')

@php
    // Index items by id for quick parent lookups
    $itemsById = $items->keyBy('id');
    // Build parent options: only items with a parent_id of null (top-level) can be parents
    $parentOptions = $items->filter(fn ($i) => is_null($i->parent_id))->map(function ($i) use ($defaultLangId) {
        $t = $i->translations->firstWhere('language_id', $defaultLangId) ?? $i->translations->first();
        return ['id' => $i->id, 'label' => $t?->label ?? '—'];
    })->values();
@endphp

<div class="max-w-6xl mx-auto" x-data="{ activeLang: {{ $defaultLangId }} }">

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800
                rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800
                rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $errors->first() }}
    </div>
    @endif

    {{-- Language tabs --}}
    @if($languages->count() > 1)
    <div class="mb-5 flex items-center gap-1 bg-white border border-gray-200 rounded-xl p-1 inline-flex">
        @foreach($languages as $lang)
        <button type="button"
                @click="activeLang = {{ $lang->id }}"
                :class="activeLang === {{ $lang->id }}
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'"
                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors">
            {{ $lang->name }}
            @if($lang->is_default)
            <span class="ml-1 opacity-60">•</span>
            @endif
        </button>
        @endforeach
    </div>
    @endif

    {{-- The main edit form is structured so NO other <form> tags are ever nested
         inside it (browsers flatten nested forms, which broke x-show and _method).
         The sidebar's Save button + location checkboxes use HTML5 form="menu-form"
         to post into this form from outside the DOM boundary. --}}
    <form method="POST" action="{{ route('contensio.account.menus.update', $menu->id) }}" id="menu-form">
        @csrf @method('PUT')
    </form>

    <div class="grid grid-cols-12 gap-6">

        {{-- ── Main panel: Menu items ───────────────────────────────── --}}
        <div class="col-span-12 lg:col-span-8 space-y-4">

                {{-- Settings card --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    @php
                        $currentLabel = $menu->translations->firstWhere('language_id', $defaultLangId)?->label
                                     ?? $menu->translations->first()?->label
                                     ?? $menu->name;
                    @endphp
                    <label class="block text-sm font-medium text-gray-700 mb-1">Menu name</label>
                    <input type="text"
                           form="menu-form"
                           name="label"
                           value="{{ old('label', $currentLabel) }}"
                           maxlength="100"
                           required
                           class="w-full sm:w-96 rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                    <p class="mt-1.5 text-xs text-gray-400">Internal name shown in the menus list. Item labels — which are translatable and shown on the site — are edited per item below.</p>
                </div>

                {{-- Menu items list --}}
                <div class="bg-white rounded-xl border border-gray-200">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Menu items</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Drag the <i class="bi bi-grip-vertical"></i> handle to reorder. Set a Parent to nest an item under another.</p>
                        </div>
                        <span id="menu-items-status"
                              class="text-xs font-medium transition-opacity duration-300 opacity-0"></span>
                    </div>

                    <div class="p-4">
                        @if($items->isNotEmpty())
                        <div id="menu-items-sortable" class="space-y-3">
                            @foreach($items as $idx => $item)
                                @include('contensio::admin.menus.partials.item', [
                                    'item'           => $item,
                                    'index'          => $idx,
                                    'languages'      => $languages,
                                    'defaultLangId'  => $defaultLangId,
                                    'parentOptions'  => $parentOptions,
                                ])
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-10 border-2 border-dashed border-gray-200 rounded-lg">
                            <p class="text-sm text-gray-400">No items yet. Use the panel on the right to add items to this menu.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Sidebar ──────────────────────────────────────────────── --}}
            <aside class="col-span-12 lg:col-span-4 space-y-4">

                {{-- Save / Delete --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-20">
                    <button type="submit"
                            form="menu-form"
                            class="w-full bg-ember-500 hover:bg-ember-600 text-white font-semibold
                                   text-sm px-4 py-2.5 rounded-lg transition-colors">
                        Save Menu
                    </button>

                    <button type="button"
                            @click="$dispatch('cms:confirm', {
                                title: 'Delete menu',
                                description: 'This will remove the menu and all its items permanently.',
                                confirmLabel: 'Delete',
                                formId: 'delete-menu-form'
                            })"
                            class="mt-2 w-full border border-red-200 text-red-600 hover:bg-red-50
                                   text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        Delete Menu
                    </button>
                </div>

                {{-- Add item --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-sm font-bold text-gray-900">Add item to menu</h3>
                    </div>

                    <div class="p-4" x-data="{ addType: 'page' }">

                        {{-- Type picker --}}
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            @foreach([
                                'page' => 'Page',
                                'post' => 'Post',
                                'term' => 'Term',
                                'custom_url' => 'Custom URL',
                            ] as $typeKey => $typeLabel)
                            <button type="button"
                                    @click="addType = @js($typeKey)"
                                    :class="addType === @js($typeKey)
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
                                    class="text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                {{ $typeLabel }}
                            </button>
                            @endforeach
                        </div>

                        {{-- Page picker --}}
                        <form method="POST" action="{{ route('contensio.account.menus.items.add', $menu->id) }}"
                              x-show="addType === 'page'" class="space-y-2">
                            @csrf
                            <input type="hidden" name="type" value="page">
                            <select name="reference_id" required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white
                                           focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                                <option value="">— Choose a page —</option>
                                @foreach($pages as $pid => $title)
                                <option value="{{ $pid }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="w-full bg-ember-500 hover:bg-ember-600 text-white text-sm font-semibold
                                           px-3 py-2 rounded-lg transition-colors">
                                Add Page
                            </button>
                        </form>

                        {{-- Post picker --}}
                        <form method="POST" action="{{ route('contensio.account.menus.items.add', $menu->id) }}"
                              x-show="addType === 'post'" class="space-y-2">
                            @csrf
                            <input type="hidden" name="type" value="post">
                            <select name="reference_id" required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white
                                           focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                                <option value="">— Choose a post —</option>
                                @foreach($posts as $pid => $title)
                                <option value="{{ $pid }}">{{ $title }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="w-full bg-ember-500 hover:bg-ember-600 text-white text-sm font-semibold
                                           px-3 py-2 rounded-lg transition-colors">
                                Add Post
                            </button>
                        </form>

                        {{-- Term picker --}}
                        <form method="POST" action="{{ route('contensio.account.menus.items.add', $menu->id) }}"
                              x-show="addType === 'term'" class="space-y-2">
                            @csrf
                            <input type="hidden" name="type" value="term">
                            <select name="reference_id" required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white
                                           focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                                <option value="">— Choose a term —</option>
                                @foreach($terms as $tid => $tname)
                                <option value="{{ $tid }}">{{ $tname }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="w-full bg-ember-500 hover:bg-ember-600 text-white text-sm font-semibold
                                           px-3 py-2 rounded-lg transition-colors">
                                Add Term
                            </button>
                        </form>

                        {{-- Custom URL --}}
                        <form method="POST" action="{{ route('contensio.account.menus.items.add', $menu->id) }}"
                              x-show="addType === 'custom_url'" class="space-y-2">
                            @csrf
                            <input type="hidden" name="type" value="custom_url">
                            <input type="text" name="label" placeholder="Label" required
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                            <input type="text" name="url" placeholder="https://example.com" required
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                            <button type="submit"
                                    class="w-full bg-ember-500 hover:bg-ember-600 text-white text-sm font-semibold
                                           px-3 py-2 rounded-lg transition-colors">
                                Add Link
                            </button>
                        </form>

                    </div>
                </div>

                {{-- Locations --}}
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-sm font-bold text-gray-900">Menu locations</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Assign this menu to locations declared by the active theme.</p>
                    </div>

                    <div class="p-4 space-y-2">
                        @if(empty($themeLocations))
                        <p class="text-xs text-gray-400 italic">The active theme doesn't declare any menu locations.</p>
                        @else
                            @foreach($themeLocations as $locKey => $locLabel)
                            <label class="flex items-start gap-2.5 cursor-pointer group">
                                <input type="checkbox"
                                       form="menu-form"
                                       name="locations[{{ $locKey }}]"
                                       value="1"
                                       {{ in_array($locKey, $myAssignments) ? 'checked' : '' }}
                                       class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
                                <div>
                                    <span class="text-sm text-gray-700 group-hover:text-gray-900 font-medium">{{ $locLabel }}</span>
                                    <span class="block text-xs text-gray-400 font-mono">{{ $locKey }}</span>
                                </div>
                            </label>
                            @endforeach
                        @endif
                    </div>
                </div>

            </aside>

        </div>

    {{-- Delete form — standalone, triggered by the confirm modal. --}}
    <form id="delete-menu-form" method="POST"
          action="{{ route('contensio.account.menus.destroy', $menu->id) }}"
          class="hidden">
        @csrf @method('DELETE')
    </form>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const list   = document.getElementById('menu-items-sortable');
        const status = document.getElementById('menu-items-status');
        if (!list || typeof Sortable === 'undefined') return;

        const reorderUrl = @json(route('contensio.account.menus.items.reorder', $menu->id));
        const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content || '';

        let hideTimer;
        const flash = (text, kind = 'success') => {
            if (!status) return;
            clearTimeout(hideTimer);
            status.textContent = text;
            status.className = 'text-xs font-medium transition-opacity duration-300 ' +
                (kind === 'success' ? 'text-green-600' :
                 kind === 'error'   ? 'text-red-600'   : 'text-gray-500') +
                ' opacity-100';
            hideTimer = setTimeout(() => { status.classList.replace('opacity-100', 'opacity-0'); }, 1500);
        };

        new Sortable(list, {
            animation: 150,
            handle: '.menu-item-handle',
            ghostClass: 'opacity-40',
            chosenClass: 'ring-2',
            dragClass: 'shadow-lg',
            onEnd: async () => {
                const ids = [...list.querySelectorAll(':scope > [data-item-id]')]
                    .map(n => parseInt(n.dataset.itemId, 10))
                    .filter(Number.isFinite);

                flash('Saving…', 'info');
                try {
                    const res = await fetch(reorderUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept':       'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ ids }),
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    flash('Saved', 'success');
                } catch (err) {
                    console.error('Reorder failed:', err);
                    flash('Could not save — reload and try again', 'error');
                }
            },
        });
    });
</script>
@endpush

@endsection
