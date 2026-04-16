{{--
 | Contensio - The open content platform for Laravel.
 | Admin — partials icon-picker-modal.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

{{-- Global Icon Picker Modal — lazy-loads Bootstrap Icons on first open.
     Trigger from any element: add the data-icon-picker attribute to an <input>.
     The preview span (icon-picker-preview) also opens the picker when clicked.
--}}

<div id="iconPickerModal"
     x-data="{
         isOpen: false,
         init() {
             window.iconPickerClose = () => { this.isOpen = false; };
         }
     }"
     @cms:iconpicker.window="isOpen = true"
     x-show="isOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
         @click="isOpen = false"></div>

    {{-- Panel --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col"
         style="max-height: 85vh;"
         @click.stop>

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 shrink-0">
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Choose Icon
            </h3>
            <button type="button"
                    @click="isOpen = false"
                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Search bar --}}
        <div class="px-5 py-3 border-b border-gray-100 shrink-0">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </div>
                <input type="text"
                       id="iconPickerSearch"
                       placeholder="Search icons…"
                       autocomplete="off"
                       class="w-full pl-9 pr-9 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                <button type="button"
                        id="iconPickerClearSearch"
                        class="hidden absolute inset-y-0 right-2 flex items-center px-1.5 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex items-center justify-between mt-2">
                <span id="iconPickerCount" class="text-xs text-gray-400"></span>
                <button type="button"
                        id="iconPickerClearBtn"
                        class="hidden text-xs font-medium text-red-500 hover:text-red-700 transition-colors">
                    Clear selection
                </button>
            </div>
        </div>

        {{-- Scrollable body --}}
        <div class="flex-1 overflow-y-auto min-h-0">

            {{-- Loading --}}
            <div id="iconPickerLoading" class="flex flex-col items-center justify-center py-16">
                <svg class="animate-spin w-7 h-7 text-blue-500 mb-3" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <p class="text-sm text-gray-400">Loading icons…</p>
            </div>

            {{-- Grid --}}
            <div id="iconPickerGrid" class="hidden p-3"></div>

            {{-- Empty state --}}
            <div id="iconPickerEmpty" class="hidden flex flex-col items-center justify-center py-16">
                <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <p class="text-sm text-gray-400">No icons found</p>
            </div>

        </div>
    </div>
</div>

<style>
.icon-picker-cell {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 1.25rem;
    cursor: pointer;
    border: 1px solid transparent;
    background: transparent;
    color: #374151;
    transition: background 0.1s, border-color 0.1s, color 0.1s;
}
.icon-picker-cell:hover {
    background: #f3f4f6;
    border-color: #e5e7eb;
}
.icon-picker-cell.is-active {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
.icon-picker-grid-wrap {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(52px, 1fr));
    gap: 3px;
}
</style>

<script>
(function () {
    'use strict';

    var ICONS_URL = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.json';

    var modal       = null;
    var searchInput = null;
    var clearSearch = null;
    var clearBtn    = null;
    var grid        = null;
    var loading     = null;
    var empty       = null;
    var countEl     = null;

    var iconNames    = null;   // full list ["bi-house", ...]
    var activeInput  = null;   // the <input> we're filling
    var allCells     = [];     // [{el, name}]
    var searchTimer  = null;

    function init() {
        modal       = document.getElementById('iconPickerModal');
        searchInput = document.getElementById('iconPickerSearch');
        clearSearch = document.getElementById('iconPickerClearSearch');
        clearBtn    = document.getElementById('iconPickerClearBtn');
        grid        = document.getElementById('iconPickerGrid');
        loading     = document.getElementById('iconPickerLoading');
        empty       = document.getElementById('iconPickerEmpty');
        countEl     = document.getElementById('iconPickerCount');

        if (!modal) return;

        // Click on [data-icon-picker] input OR its preview sibling
        document.addEventListener('click', function (e) {
            var input = e.target.closest('[data-icon-picker]');
            if (!input) {
                var preview = e.target.closest('.icon-picker-preview');
                if (preview) {
                    input = preview.parentElement ? preview.parentElement.querySelector('[data-icon-picker]') : null;
                }
            }
            if (input) {
                e.preventDefault();
                openPicker(input);
            }
        });

        // Live-update the preview span when value changes
        document.addEventListener('change', function (e) {
            var input = e.target.closest('[data-icon-picker]');
            if (!input) return;
            var preview = input.parentElement ? input.parentElement.querySelector('.icon-picker-preview i') : null;
            if (preview) {
                preview.className = 'bi ' + (input.value || 'bi-question-circle');
                if (!input.value) preview.className = '';
            }
        });

        // Search
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(filterIcons, 150);
            clearSearch.classList.toggle('hidden', !this.value);
        });

        clearSearch.addEventListener('click', function () {
            searchInput.value = '';
            clearSearch.classList.add('hidden');
            filterIcons();
            searchInput.focus();
        });

        clearBtn.addEventListener('click', function () {
            selectIcon('');
        });

        // Reset on close
        modal.addEventListener('cms:close', function () {
            searchInput.value = '';
            clearSearch.classList.add('hidden');
            if (iconNames) filterIcons();
        });

        // Focus search when modal opens (Alpine sets isOpen, then the element becomes visible)
        var observer = new MutationObserver(function () {
            if (modal.style.display !== 'none') {
                setTimeout(function () { searchInput && searchInput.focus(); }, 50);
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    }

    function openPicker(input) {
        activeInput = input;

        // Show/hide "Clear selection" button
        clearBtn.classList.toggle('hidden', !input.value);

        // Open modal via Alpine event
        window.dispatchEvent(new CustomEvent('cms:iconpicker'));

        // Load or highlight
        if (!iconNames) {
            loadIcons();
        } else {
            highlightCurrent(input.value);
        }
    }

    function loadIcons() {
        loading.classList.remove('hidden');
        grid.classList.add('hidden');
        empty.classList.add('hidden');

        fetch(ICONS_URL)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                iconNames = Object.keys(data).map(function (n) { return 'bi-' + n; });
                iconNames.sort();
                renderGrid();
            })
            .catch(function () {
                loading.innerHTML = '<p class="text-sm text-red-500 py-8 text-center">Failed to load icons.</p>';
            });
    }

    function renderGrid() {
        loading.classList.add('hidden');
        grid.classList.remove('hidden');

        var wrap = document.createElement('div');
        wrap.className = 'icon-picker-grid-wrap';

        allCells = [];

        for (var i = 0; i < iconNames.length; i++) {
            var name = iconNames[i];
            var btn  = document.createElement('button');
            btn.type      = 'button';
            btn.className = 'icon-picker-cell';
            btn.dataset.icon = name;
            btn.title    = name;
            btn.innerHTML = '<i class="bi ' + name + '"></i>';
            btn.addEventListener('click', function () { selectIcon(this.dataset.icon); });
            wrap.appendChild(btn);
            allCells.push({ el: btn, name: name });
        }

        grid.innerHTML = '';
        grid.appendChild(wrap);

        countEl.textContent = iconNames.length + ' icons';
        highlightCurrent(activeInput ? activeInput.value : '');
    }

    function selectIcon(name) {
        if (activeInput) {
            activeInput.value = name;
            activeInput.dispatchEvent(new Event('input',  { bubbles: true }));
            activeInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (window.iconPickerClose) window.iconPickerClose();
        modal.dispatchEvent(new CustomEvent('cms:close'));
    }

    function filterIcons() {
        var query = searchInput.value.toLowerCase().trim().replace(/^bi-/, '');
        var count = 0;

        for (var i = 0; i < allCells.length; i++) {
            var match = !query
                || allCells[i].name.indexOf(query) !== -1
                || allCells[i].name.replace('bi-', '').replace(/-/g, ' ').indexOf(query) !== -1;
            allCells[i].el.classList.toggle('hidden', !match);
            if (match) count++;
        }

        countEl.textContent = query
            ? (count + ' of ' + iconNames.length + ' icons')
            : (iconNames.length + ' icons');
        empty.classList.toggle('hidden', count > 0);
        grid.classList.toggle('hidden', count === 0);
    }

    function highlightCurrent(value) {
        for (var i = 0; i < allCells.length; i++) {
            allCells[i].el.classList.toggle('is-active', allCells[i].name === value);
        }
    }

    // Public API
    window.openIconPicker = function (input) { openPicker(input); };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
