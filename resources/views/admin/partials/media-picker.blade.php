{{--
 | Reusable Media Library picker modal.
 |
 | Include once per page (e.g. in the content edit layout). Fire
 |   $dispatch('cms:media-pick', { inputName: 'fields[12]', accept: 'image/', multiple: false })
 | and when the user picks, the picker writes the media ID (or array of IDs)
 | into the hidden input with that name.
 |
 | Expects the caller to already have a hidden <input> with the given name
 | and data-media-preview attribute where a thumbnail strip can render.
--}}

<div x-data="cmsMediaPicker()"
     x-on:cms:media-pick.window="open($event.detail)"
     x-show="opened"
     x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">

    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="close()"></div>

    <div x-show="opened"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl mx-auto flex flex-col overflow-hidden"
         style="height: 85vh;">

        {{-- Header --}}
        <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-100 shrink-0">
            <h3 class="text-base font-semibold text-gray-900">Media Library</h3>
            <div class="flex-1">
                <input type="search" x-model.debounce.300ms="q"
                       @input="reload()"
                       placeholder="Search files…"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500">
            </div>
            <button type="button" @click="$refs.fileUpload.click()"
                    class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
                <i class="bi bi-upload"></i> Upload
            </button>
            <input type="file" x-ref="fileUpload" class="hidden" @change="upload($event)">
            <button type="button" @click="close()" class="text-gray-400 hover:text-gray-700">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>

        {{-- Grid --}}
        <div class="flex-1 overflow-y-auto p-5">
            <template x-if="loading && items.length === 0">
                <div class="flex items-center justify-center h-full text-gray-400">
                    <i class="bi bi-hourglass-split mr-2"></i> Loading…
                </div>
            </template>

            <template x-if="! loading && items.length === 0">
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <i class="bi bi-images text-4xl mb-3"></i>
                    <p>No files found.</p>
                </div>
            </template>

            <div x-show="items.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <template x-for="item in items" :key="item.id">
                    <button type="button" @click="toggle(item)"
                            class="group relative aspect-square rounded-lg overflow-hidden border-2 transition-all"
                            :class="isSelected(item) ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-400'">
                        <template x-if="item.is_image">
                            <img :src="item.url" :alt="item.file_name" class="w-full h-full object-cover">
                        </template>
                        <template x-if="! item.is_image">
                            <div class="w-full h-full bg-gray-100 flex flex-col items-center justify-center text-gray-500 p-2">
                                <i class="bi bi-file-earmark text-3xl mb-1"></i>
                                <span class="text-xs font-mono break-all" x-text="item.file_name"></span>
                            </div>
                        </template>
                        <div x-show="isSelected(item)" class="absolute top-1 right-1 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center">
                            <i class="bi bi-check-lg text-sm"></i>
                        </div>
                    </button>
                </template>
            </div>

            <div x-show="page < pages" class="mt-5 text-center">
                <button type="button" @click="loadMore()"
                        class="text-sm font-medium text-ember-600 hover:text-ember-700">
                    Load more
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between gap-3 px-6 py-3 bg-gray-50 border-t border-gray-100 shrink-0">
            <p class="text-sm text-gray-500">
                <span x-text="selected.length"></span>
                <span x-show="multiple"> selected (max unlimited)</span>
                <span x-show="! multiple && selected.length > 0"> selected</span>
            </p>
            <div class="flex items-center gap-3">
                <button type="button" @click="close()" class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2">Cancel</button>
                <button type="button" @click="confirm()" :disabled="selected.length === 0"
                        class="bg-ember-500 hover:bg-ember-600 disabled:bg-gray-300 text-white font-semibold text-sm px-5 py-2 rounded-lg transition-colors">
                    Use selected
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cmsMediaPicker() {
    return {
        opened:    false,
        inputName: null,
        multiple:  false,
        accept:    '',            // MIME prefix, e.g. "image/"
        q:         '',
        page:      1,
        pages:     1,
        items:     [],
        selected:  [],
        loading:   false,

        open(detail = {}) {
            this.inputName = detail.inputName || null;
            this.multiple  = !! detail.multiple;
            this.accept    = detail.accept || '';
            this.q         = '';
            this.page      = 1;
            this.pages     = 1;
            this.items     = [];
            this.selected  = [];
            this.opened    = true;
            this.reload();
        },
        close() { this.opened = false; },
        reload() {
            this.page = 1;
            this.items = [];
            this.fetch();
        },
        loadMore() {
            this.page += 1;
            this.fetch(true);
        },
        async fetch(append = false) {
            this.loading = true;
            const url = new URL(`{{ route('contensio.account.media.pick') }}`, window.location.origin);
            url.searchParams.set('q', this.q);
            url.searchParams.set('mime', this.accept);
            url.searchParams.set('page', this.page);
            try {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.items = append ? [...this.items, ...data.items] : data.items;
                this.pages = data.pages;
            } finally {
                this.loading = false;
            }
        },
        async upload(event) {
            const file = event.target.files[0];
            if (! file) return;
            const fd = new FormData();
            fd.append('file', file);
            fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            const res = await fetch(`{{ route('contensio.account.media.pick.upload') }}`, { method: 'POST', body: fd });
            if (res.ok) {
                const data = await res.json();
                this.items.unshift(data.item);
                this.toggle(data.item);
            }
            event.target.value = '';
        },
        toggle(item) {
            const idx = this.selected.findIndex(s => s.id === item.id);
            if (idx >= 0) {
                this.selected.splice(idx, 1);
            } else {
                if (this.multiple) this.selected.push(item);
                else this.selected = [item];
            }
        },
        isSelected(item) { return this.selected.some(s => s.id === item.id); },
        confirm() {
            if (this.selected.length === 0 || ! this.inputName) { this.close(); return; }

            // Write the selection into the hidden input by name
            const hidden = document.querySelector(`input[name="${CSS.escape(this.inputName)}"]`);
            if (hidden) {
                hidden.value = this.multiple
                    ? JSON.stringify(this.selected.map(s => s.id))
                    : String(this.selected[0].id);
            }

            // Update the visual preview strip if present
            const preview = document.querySelector(`[data-media-preview="${CSS.escape(this.inputName)}"]`);
            if (preview) {
                preview.innerHTML = this.selected.map(s => `
                    <div class="relative w-20 h-20 rounded-lg overflow-hidden border border-gray-200 bg-white flex items-center justify-center">
                        ${s.is_image
                            ? `<img src="${s.url}" class="w-full h-full object-cover" alt="">`
                            : `<div class="text-[10px] p-1 font-mono break-all text-gray-500 text-center">${s.file_name}</div>`}
                    </div>
                `).join('');
            }

            // Notify other components listening for media selection (e.g. featured image card)
            window.dispatchEvent(new CustomEvent('cms:media-selected', {
                detail: { inputName: this.inputName, items: this.selected }
            }));

            this.close();
        },
    };
}
</script>
