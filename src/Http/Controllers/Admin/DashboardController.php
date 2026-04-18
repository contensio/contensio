<?php

/**
 * Contensio - The open content platform for Laravel.
 * A flexible content foundation for blogs, shops, communities,
 * and any content-driven app.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Admin;

use Contensio\Models\ActivityLog;
use Contensio\Models\Content;
use Contensio\Models\ContentType;
use Contensio\Models\Language;
use Contensio\Models\Media;
use Contensio\Services\VersionChecker;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $defaultLangId = Language::where('is_default', true)->value('id')
            ?? Language::orderBy('position')->value('id');

        // Resolve page/post content-type IDs for fast counting
        $pageTypeId = ContentType::where('name', 'page')->value('id');
        $postTypeId = ContentType::where('name', 'post')->value('id');

        $stats = [
            'pages'  => $pageTypeId ? Content::where('content_type_id', $pageTypeId)->count() : 0,
            'posts'  => $postTypeId ? Content::where('content_type_id', $postTypeId)->count() : 0,
            'media'  => Media::count(),
            'users'  => \App\Models\User::count(),
            'drafts' => Content::where('status', Content::STATUS_DRAFT)->count(),
        ];

        // Recent published content — most recent 6
        $recentPublished = Content::with([
                'contentType',
                'author',
                'translations' => fn ($q) => $q->where('language_id', $defaultLangId),
            ])
            ->where('status', Content::STATUS_PUBLISHED)
            ->latest('published_at')
            ->limit(6)
            ->get();

        // Recent drafts — most recently touched
        $recentDrafts = Content::with([
                'contentType',
                'author',
                'translations' => fn ($q) => $q->where('language_id', $defaultLangId),
            ])
            ->where('status', Content::STATUS_DRAFT)
            ->latest('updated_at')
            ->limit(5)
            ->get();

        // Activity log — most recent 8
        $recentActivity = ActivityLog::with('user')
            ->latest('created_at')
            ->limit(8)
            ->get();

        $updateInfo = VersionChecker::updateInfo();

        return view('contensio::admin.dashboard', compact(
            'stats',
            'recentPublished',
            'recentDrafts',
            'recentActivity',
            'defaultLangId',
            'updateInfo',
        ));
    }
}
