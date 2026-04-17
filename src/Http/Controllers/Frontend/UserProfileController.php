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

use App\Models\User;
use Contensio\Models\Content;
use Contensio\Models\ContentType;
use Contensio\Models\Setting;
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

        $site = [
            'name'    => Setting::where('module', 'core')->where('setting_key', 'site_name')->value('value') ?? config('contensio.name', 'Contensio'),
            'tagline' => Setting::where('module', 'core')->where('setting_key', 'site_tagline')->value('value') ?? '',
        ];

        return view('contensio::frontend.author', compact('user', 'posts', 'site'));
    }
}
