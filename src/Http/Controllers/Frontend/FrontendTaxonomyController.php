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

use Contensio\Support\ThemeTemplateResolver;
use Contensio\Models\Content;
use Contensio\Models\Language;
use Contensio\Support\SiteConfig;
use Contensio\Models\TaxonomyTranslation;
use Contensio\Models\TermTranslation;
use Illuminate\Routing\Controller;

class FrontendTaxonomyController extends Controller
{
    /**
     * Display all published posts tagged with a specific taxonomy term.
     * URL pattern: /{taxonomy-slug}/{term-slug}
     */
    public function term(string $taxonomySlug, string $termSlug)
    {
        $lang = $this->defaultLang();

        // Resolve taxonomy by its URL slug
        $taxTrans = TaxonomyTranslation::where('slug', $taxonomySlug)
            ->where('language_id', $lang?->id)
            ->with('taxonomy')
            ->first();

        if (! $taxTrans) {
            abort(404);
        }

        $taxonomy = $taxTrans->taxonomy;

        // Resolve term by its URL slug within this taxonomy
        $termTrans = TermTranslation::where('slug', $termSlug)
            ->where('language_id', $lang?->id)
            ->whereHas('term', fn ($q) => $q->where('taxonomy_id', $taxonomy->id))
            ->with('term')
            ->first();

        if (! $termTrans) {
            abort(404);
        }

        $term = $termTrans->term;

        $perPage = max(1, intval(
            Setting::where('module', 'reading')->where('setting_key', 'posts_per_page')->value('value') ?? 12
        ));

        $posts = Content::where('status', 'published')
            ->whereHas('terms', fn ($q) => $q->where('terms.id', $term->id))
            ->with([
                'translations' => fn ($q) => $q->where('language_id', $lang?->id),
                'author',
                'featuredImage',
            ])
            ->latest('published_at')
            ->paginate($perPage);

        $site = $this->siteConfig();

        return view(ThemeTemplateResolver::taxonomy($taxonomySlug, $taxonomy->is_hierarchical), compact(
            'taxonomy', 'taxTrans', 'term', 'termTrans', 'posts', 'site', 'lang'
        ));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function siteConfig(): array
    {
        return SiteConfig::all();
    }

    private function defaultLang(): ?Language
    {
        return Language::where('is_default', true)->first()
            ?? Language::where('status', '!=', 'disabled')->orderBy('position')->first();
    }
}
