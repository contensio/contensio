<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Frontend;

use Contensio\Models\Content;
use Contensio\Models\ContentType;
use Contensio\Models\Language;
use Contensio\Models\Setting;
use Illuminate\Routing\Controller;

class FeedController extends Controller
{
    public function index()
    {
        $lang = Language::where('is_default', true)->first()
            ?? Language::where('status', '!=', 'disabled')->orderBy('position')->first();

        $coreSettings = Setting::where('module', 'core')
            ->whereIn('setting_key', ['site_name', 'site_tagline'])
            ->pluck('value', 'setting_key');

        $postType = ContentType::where('name', 'post')->first();

        $posts = $postType
            ? Content::where('content_type_id', $postType->id)
                ->where('status', 'published')
                ->with([
                    'translations' => fn ($q) => $q->where('language_id', $lang?->id),
                    'author',
                ])
                ->latest('published_at')
                ->limit(20)
                ->get()
            : collect();

        $site = [
            'name'    => $coreSettings['site_name']    ?? config('app.name'),
            'tagline' => $coreSettings['site_tagline'] ?? '',
            'url'     => url('/'),
        ];

        return response()
            ->view('contensio::frontend.feed', compact('posts', 'site', 'lang'))
            ->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }
}
