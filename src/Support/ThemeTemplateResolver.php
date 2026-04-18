<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Support;

/**
 * Resolves the correct theme template for any frontend context.
 *
 * Works exactly like the WordPress template hierarchy: each context provides an
 * ordered list of candidate template names from most specific to least specific.
 * The first candidate that exists in the active theme wins. If nothing matches,
 * theme::index is the ultimate catch-all fallback.
 *
 * Theme developers override behaviour by creating specific templates without
 * touching core code:
 *
 *   single-product.blade.php  →  overrides single.blade.php for 'product' type
 *   taxonomy-genre.blade.php  →  overrides taxonomy.blade.php for 'genre' taxonomy
 *   page-about.blade.php      →  overrides page.blade.php for the /about page
 */
class ThemeTemplateResolver
{
    /**
     * Return the first existing theme template from the candidate list.
     * Falls back to theme::index if nothing in the list exists.
     */
    public static function resolve(array $candidates): string
    {
        foreach ($candidates as $template) {
            if ($template && view()->exists("theme::{$template}")) {
                return "theme::{$template}";
            }
        }

        return 'theme::index';
    }

    /**
     * Homepage.
     *
     * Static-page mode:  front-page → page  → index
     * Blog-index mode:   home → index
     */
    public static function home(bool $isStaticPage): string
    {
        return static::resolve(
            $isStaticPage
                ? ['front-page', 'page', 'index']
                : ['home', 'index']
        );
    }

    /**
     * Static page.
     *
     * page-{slug} → page → index
     */
    public static function page(string $slug): string
    {
        return static::resolve(["page-{$slug}", 'page', 'index']);
    }

    /**
     * Single content entry (post or any custom type).
     *
     * single-{type}-{slug} → single-{type} → single → index
     *
     * For static pages use page() instead.
     */
    public static function single(string $type, string $slug): string
    {
        return static::resolve([
            "single-{$type}-{$slug}",
            "single-{$type}",
            'single',
            'index',
        ]);
    }

    /**
     * Content-type archive (paginated list of entries of one type).
     *
     * archive-{type} → archive → index
     */
    public static function archive(string $type): string
    {
        return static::resolve(["archive-{$type}", 'archive', 'index']);
    }

    /**
     * Taxonomy term archive.
     *
     * taxonomy-{slug} → category|tag → taxonomy → archive → index
     *
     * Hierarchical taxonomies (category-like) use 'category' as their
     * named fallback. Flat taxonomies (tag-like) use 'tag'.
     */
    public static function taxonomy(string $taxonomySlug, bool $isHierarchical): string
    {
        return static::resolve([
            "taxonomy-{$taxonomySlug}",
            $isHierarchical ? 'category' : 'tag',
            'taxonomy',
            'archive',
            'index',
        ]);
    }

    /**
     * Author profile / post archive.
     *
     * author → archive → index
     */
    public static function author(): string
    {
        return static::resolve(['author', 'archive', 'index']);
    }

    /**
     * Search results.
     *
     * search → index
     */
    public static function search(): string
    {
        return static::resolve(['search', 'index']);
    }

    /**
     * 404 not found.
     *
     * 404 → index
     */
    public static function notFound(): string
    {
        return static::resolve(['404', 'index']);
    }
}
