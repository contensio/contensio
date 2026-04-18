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

use Contensio\Support\SiteConfig;
use Contensio\Support\ThemeTemplateResolver;
use App\Models\User;
use Contensio\Models\Content;
use Contensio\Models\ContentType;
use Contensio\Models\Language;
use Illuminate\Routing\Controller;

class UserProfileController extends Controller
{
    public function show(string $code)
    {
        $user = User::where('code', $code)->where('is_active', true)->firstOrFail();

        $postType = ContentType::where('name', 'post')->first();

        $posts = $postType
            ? Content::where('content_type_id', $postType->id)
                ->where('status', 'published')
                ->where('author_id', $user->id)
                ->with(['translations', 'featuredImage'])
                ->latest('published_at')
                ->take(10)
                ->get()
            : collect();

        $site = SiteConfig::all();
        $lang = Language::where('is_default', true)->first()
            ?? Language::where('status', '!=', 'disabled')->orderBy('position')->first();

        return view(ThemeTemplateResolver::author(), compact('user', 'posts', 'site', 'lang'));
    }
}
