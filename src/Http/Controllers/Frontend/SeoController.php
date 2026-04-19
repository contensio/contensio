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

namespace Contensio\Http\Controllers\Frontend;

use Contensio\Models\Content;
use Contensio\Models\ContentTranslation;
use Contensio\Models\ContentType;
use Contensio\Models\Language;
use Contensio\Models\Setting;
use Contensio\Models\Taxonomy;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SeoController extends Controller
{
    /**
     * Serve /sitemap.xml listing the home page, blog index, and every
     * published page/post slug for every active language.
     *
     * Respects the core.seo_noindex setting — returns an empty sitemap
     * when the whole site is set to noindex.
     */
    public function sitemap()
    {
        // If the site is globally noindexed, serve an empty sitemap
        $noIndex = (bool) Setting::where('module', 'core')->where('setting_key', 'seo_noindex')->value('value');

        $urls = [];
        $now  = now()->toAtomString();

        if (! $noIndex) {
            $defaultLang = Language::where('is_default', true)->first()
                ?? Language::active()->orderBy('position')->first();

            // Home + Blog roots
            if ($defaultLang) {
                $urls[] = [
                    'type'       => 'homepage',
                    'loc'        => route('contensio.home'),
                    'lastmod'    => $now,
                    'changefreq' => 'daily',
                    'priority'   => '1.0',
                ];
                $urls[] = [
                    'type'       => 'blog_index',
                    'loc'        => route('contensio.blog'),
                    'lastmod'    => $now,
                    'changefreq' => 'daily',
                    'priority'   => '0.9',
                ];
            }

            // Resolve page + post type IDs
            $pageTypeId = ContentType::where('name', 'page')->value('id');
            $postTypeId = ContentType::where('name', 'post')->value('id');

            // Pages
            if ($pageTypeId) {
                $pages = ContentTranslation::query()
                    ->select('content_translations.slug', 'contents.updated_at')
                    ->join('contents', 'contents.id', '=', 'content_translations.content_id')
                    ->where('contents.content_type_id', $pageTypeId)
                    ->where('contents.status', Content::STATUS_PUBLISHED)
                    ->whereNotNull('content_translations.slug')
                    ->get();
                foreach ($pages as $row) {
                    $urls[] = [
                        'type'       => 'page',
                        'loc'        => route('contensio.page', $row->slug),
                        'lastmod'    => optional($row->updated_at)->toAtomString() ?? $now,
                        'changefreq' => 'weekly',
                        'priority'   => '0.7',
                    ];
                }
            }

            // Posts
            if ($postTypeId) {
                $posts = ContentTranslation::query()
                    ->select('content_translations.slug', 'contents.updated_at', 'contents.published_at')
                    ->join('contents', 'contents.id', '=', 'content_translations.content_id')
                    ->where('contents.content_type_id', $postTypeId)
                    ->where('contents.status', Content::STATUS_PUBLISHED)
                    ->whereNotNull('content_translations.slug')
                    ->get();
                foreach ($posts as $row) {
                    $urls[] = [
                        'type'       => 'post',
                        'loc'        => route('contensio.post', $row->slug),
                        'lastmod'    => optional($row->updated_at ?? $row->published_at)->toAtomString() ?? $now,
                        'changefreq' => 'weekly',
                        'priority'   => '0.6',
                    ];
                }
            }

            // Taxonomy term archive pages
            if ($defaultLang) {
                $taxonomies = Taxonomy::with([
                    'translations' => fn ($q) => $q->where('language_id', $defaultLang->id),
                    'terms.translations' => fn ($q) => $q->where('language_id', $defaultLang->id),
                ])->get();

                foreach ($taxonomies as $taxonomy) {
                    $taxTrans = $taxonomy->translations->first();
                    if (! $taxTrans?->slug) {
                        continue;
                    }
                    foreach ($taxonomy->terms as $term) {
                        $termTrans = $term->translations->first();
                        if (! $termTrans?->slug) {
                            continue;
                        }
                        $urls[] = [
                            'type'       => 'taxonomy_term',
                            'loc'        => route('contensio.taxonomy.term', [$taxTrans->slug, $termTrans->slug]),
                            'lastmod'    => $now,
                            'changefreq' => 'weekly',
                            'priority'   => '0.5',
                        ];
                    }
                }
            }
        }

        // Allow plugins (e.g. Sitemap Generator) to add, remove, or modify entries.
        // Signature: fn(array $urls): array
        $urls = \Contensio\Support\Hook::applyFilters('contensio/seo/sitemap-urls', $urls);

        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . "</lastmod>\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n"; // Note: 'type' key is internal metadata only — not written to XML
        }
        $xml .= '</urlset>';

        return response($xml, Response::HTTP_OK, [
            'Content-Type'  => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Serve /robots.txt. When the site is globally noindexed, disallow all
     * crawlers. Otherwise, allow everything and point to the sitemap.
     *
     * Admins can override the body entirely via core.robots_txt setting.
     */
    public function robots()
    {
        // Custom override (admin-provided)
        $custom = Setting::where('module', 'core')->where('setting_key', 'robots_txt')->value('value');
        if ($custom) {
            return response($custom, Response::HTTP_OK, [
                'Content-Type'  => 'text/plain; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        $noIndex = (bool) Setting::where('module', 'core')->where('setting_key', 'seo_noindex')->value('value');

        $lines = [];
        if ($noIndex) {
            $lines[] = 'User-agent: *';
            $lines[] = 'Disallow: /';
        } else {
            $lines[] = 'User-agent: *';
            $lines[] = 'Disallow: /' . ltrim(config('contensio.route_prefix', 'admin'), '/') . '/';
            $lines[] = '';
            $lines[] = 'Sitemap: ' . url('/sitemap.xml');
        }

        return response(implode("\n", $lines) . "\n", Response::HTTP_OK, [
            'Content-Type'  => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
