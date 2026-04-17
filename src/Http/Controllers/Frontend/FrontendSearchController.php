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

use Contensio\Models\ContentTranslation;
use Contensio\Models\ContentType;
use Contensio\Models\Language;
use Contensio\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class FrontendSearchController extends Controller
{
    public function index(Request $request)
    {
        $lang  = $this->defaultLang();
        $site  = $this->siteConfig();
        $query = trim((string) $request->input('q', ''));

        $results  = new \Illuminate\Pagination\LengthAwarePaginator(
            collect(), 0, 12
        );
        $searched = false;

        if (strlen($query) >= 2) {
            $searched = true;

            // Search across posts and pages
            $typeIds = ContentType::whereIn('name', ['post', 'page'])
                ->pluck('id')
                ->all();

            if (! empty($typeIds)) {
                $results = ContentTranslation::where('language_id', $lang?->id)
                    ->where(function ($q) use ($query) {
                        $q->where('title',   'like', "%{$query}%")
                          ->orWhere('excerpt', 'like', "%{$query}%")
                          ->orWhere('body',    'like', "%{$query}%");
                    })
                    ->whereHas('content', fn ($q) => $q
                        ->whereIn('content_type_id', $typeIds)
                        ->where('status', 'published')
                    )
                    ->with(['content' => fn ($q) => $q->with(['featuredImage', 'author'])])
                    ->latest('id')
                    ->paginate(12)
                    ->withQueryString();
            }
        }

        return view('theme::search', compact('results', 'query', 'searched', 'site', 'lang'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function siteConfig(): array
    {
        $settings = Setting::where('module', 'core')
            ->whereIn('setting_key', ['site_name', 'site_tagline'])
            ->pluck('value', 'setting_key');

        return [
            'name'    => $settings['site_name']    ?? config('app.name'),
            'tagline' => $settings['site_tagline'] ?? '',
        ];
    }

    private function defaultLang(): ?Language
    {
        return Language::where('is_default', true)->first()
            ?? Language::where('status', '!=', 'disabled')->orderBy('position')->first();
    }
}
