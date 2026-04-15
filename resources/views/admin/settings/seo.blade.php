{{--
 | Contensio - The open content platform for Laravel.
 | Admin — SEO settings.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'SEO Settings')

@section('breadcrumb')
<a href="{{ route('cms.admin.settings.index') }}" class="text-gray-400 hover:text-gray-700">Configuration</a>
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

    <form method="POST" action="{{ route('cms.admin.settings.seo.save') }}" class="space-y-4">
        @csrf

        {{-- Indexing --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-base font-bold text-gray-900 mb-1">Search engine indexing</h2>
            <p class="text-xs text-gray-500 mb-4">Keep search engines away from staging or private sites.</p>

            <label class="flex items-start gap-2.5 cursor-pointer group">
                <input type="hidden" name="seo_noindex" value="0">
                <input type="checkbox" name="seo_noindex" value="1"
                       {{ ! empty($settings['seo_noindex']) ? 'checked' : '' }}
                       class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
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

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Default Open Graph image URL</label>
                <input type="text" name="default_og_image"
                       value="{{ old('default_og_image', $settings['default_og_image'] ?? '') }}"
                       placeholder="https://example.com/og-default.png"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="mt-1 text-xs text-gray-500">Recommended: 1200 × 630 px PNG or JPG. Per-page featured images take priority when set.</p>
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
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                             focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('robots_txt', $settings['robots_txt'] ?? '') }}</textarea>
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
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                Save Changes
            </button>
        </div>
    </form>

</div>

@endsection
