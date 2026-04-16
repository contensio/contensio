{{--
 | Contensio - The open content platform for Laravel.
 | Admin — SEO settings.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'SEO Settings')

@section('breadcrumb')
<a href="{{ route('contensio.account.settings.index') }}" class="text-gray-400 hover:text-gray-700">Configuration</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">SEO</span>
@endsection

@section('content')

<div class="max-w-3xl mx-auto">

    <h1 class="text-xl font-bold text-gray-900 mb-1">SEO Settings</h1>
    <p class="text-sm text-gray-500 mb-5">Control how search engines index your site and what appears when your pages are shared.</p>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('contensio.account.settings.seo.save') }}" class="space-y-4">
        @csrf

        {{-- Indexing --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-base font-bold text-gray-900 mb-1">Search engine indexing</h2>
            <p class="text-xs text-gray-500 mb-4">Keep search engines away from staging or private sites.</p>

            <label class="flex items-start gap-2.5 cursor-pointer group">
                <input type="hidden" name="seo_noindex" value="0">
                <input type="checkbox" name="seo_noindex" value="1"
                       {{ ! empty($settings['seo_noindex']) ? 'checked' : '' }}
                       class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500">
                <div>
                    <span class="text-sm font-medium text-gray-800">Discourage search engines from indexing this site</span>
                    <p class="text-xs text-gray-500 mt-0.5">Adds <code class="bg-gray-100 px-1 rounded">noindex, nofollow</code> to every page, disallows everything in robots.txt, and returns an empty sitemap. Use on staging / private sites.</p>
                </div>
            </label>
        </div>

        {{-- Social sharing --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 mb-1">Social sharing</h2>
                <p class="text-xs text-gray-500">Default image shown when someone shares a page without its own featured image.</p>
            </div>

            <div x-data="{
                     imgUrl: @js($settings['default_og_image'] ?? null),
                     init() {
                         window.addEventListener('cms:media-selected', (ev) => {
                             if (ev.detail.inputName !== 'og_image_picker') return;
                             const url = ev.detail.items[0]?.url ?? null;
                             this.imgUrl = url;
                             this.$refs.ogInput.value = url ?? '';
                         });
                     },
                     remove() { this.imgUrl = null; this.$refs.ogInput.value = ''; },
                 }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Default Open Graph image</label>

                {{-- Hidden input submitted with the form --}}
                <input type="hidden" name="default_og_image" x-ref="ogInput"
                       value="{{ old('default_og_image', $settings['default_og_image'] ?? '') }}">

                {{-- Preview --}}
                <div x-show="imgUrl" x-cloak
                     class="relative mb-3 rounded-lg overflow-hidden border border-gray-200 bg-gray-50"
                     style="aspect-ratio: 1200/630; max-height: 190px;">
                    <img :src="imgUrl" alt="OG image" class="w-full h-full object-cover">
                    <button type="button" @click="remove()"
                            class="absolute top-2 right-2 w-7 h-7 bg-white/90 hover:bg-white rounded-full
                                   flex items-center justify-center text-gray-500 hover:text-red-600 shadow-sm transition-colors"
                            title="Remove">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Placeholder --}}
                <div x-show="! imgUrl"
                     class="mb-3 rounded-lg border-2 border-dashed border-gray-200 bg-gray-50
                            flex flex-col items-center justify-center text-gray-400 py-8">
                    <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-xs">No image selected</p>
                </div>

                <button type="button"
                        @click="$dispatch('cms:media-pick', { inputName: 'og_image_picker', accept: 'image/', multiple: false })"
                        class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 border border-gray-300
                               hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors"
                        x-text="imgUrl ? 'Change Image' : 'Select from Media Library'">
                </button>
                <p class="mt-2 text-xs text-gray-500">Recommended: 1200 × 630 px PNG or JPG. Per-page featured images take priority when set.</p>
            </div>
        </div>

        {{-- Verification --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 mb-1">Verification</h2>
                <p class="text-xs text-gray-500">Prove you own this site to search engines.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Google Search Console verification code</label>
                <input type="text" name="google_site_verification"
                       value="{{ old('google_site_verification', $settings['google_site_verification'] ?? '') }}"
                       placeholder="aBcDeF1234-XyZ"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono
                              focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">The token from the HTML tag method — paste only the <code class="bg-gray-100 px-1 rounded">content="…"</code> value.</p>
            </div>
        </div>

        {{-- robots.txt --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 mb-1">robots.txt override</h2>
                <p class="text-xs text-gray-500">Leave blank to use the automatic default (allows everything except /admin, points to the sitemap).</p>
            </div>

            <textarea name="robots_txt" rows="8"
                      placeholder="# Leave blank for the default&#10;User-agent: *&#10;Disallow: /admin/&#10;Sitemap: https://example.com/sitemap.xml"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono resize-y
                             focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">{{ old('robots_txt', $settings['robots_txt'] ?? '') }}</textarea>
        </div>

        <div class="flex items-center justify-between text-sm">
            <div class="flex items-center gap-4 text-gray-500">
                <a href="{{ url('/sitemap.xml') }}" target="_blank" class="hover:text-blue-600 inline-flex items-center gap-1">
                    View sitemap.xml
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
                <a href="{{ url('/robots.txt') }}" target="_blank" class="hover:text-blue-600 inline-flex items-center gap-1">
                    View robots.txt
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>

            <button type="submit"
                    class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                Save Changes
            </button>
        </div>
    </form>

</div>

@endsection
