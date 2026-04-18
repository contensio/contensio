<?php

/**
 * Contensio - The open content platform for Laravel.
 * Admin — global search.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Admin;

use Contensio\Models\ContentTranslation;
use Contensio\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q       = trim((string) $request->input('q', ''));
        $results = null;

        if (mb_strlen($q) >= 2) {
            $content = ContentTranslation::where('title', 'like', '%' . $q . '%')
                ->orWhere('excerpt', 'like', '%' . $q . '%')
                ->whereHas('content', fn ($query) => $query->whereNotIn('status', ['trashed']))
                ->with(['content.contentType.translations', 'content.author'])
                ->orderByDesc('id')
                ->limit(40)
                ->get()
                // Deduplicate: one row per content_id (multiple lang translations may match)
                ->unique('content_id')
                ->values();

            $media = Media::where('file_name', 'like', '%' . $q . '%')
                ->orderByDesc('id')
                ->limit(12)
                ->get();

            $results = compact('content', 'media');
        }

        return view('contensio::admin.search.index', compact('q', 'results'));
    }
}
