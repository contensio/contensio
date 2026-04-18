<?php

/**
 * Contensio - The open content platform for Laravel.
 * Public read-only JSON API for headless usage.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Api;

use Contensio\Models\Content;
use Contensio\Models\ContentTranslation;
use Contensio\Models\ContentType;
use Contensio\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ContentApiController extends Controller
{
    /**
     * GET /api/v1/content/{type}
     * Returns paginated published entries for a content type.
     *
     * Query params:
     *   per_page  int   1–100 (default 20)
     *   lang      string  Language code (defaults to site default language)
     */
    public function index(Request $request, string $type): JsonResponse
    {
        $contentType = ContentType::where('name', $type)->first();

        if (! $contentType) {
            return response()->json(['error' => 'Content type not found.'], 404);
        }

        $lang    = $this->resolveLanguage($request);
        $perPage = min((int) $request->input('per_page', 20), 100);

        $items = Content::where('content_type_id', $contentType->id)
            ->where('status', 'published')
            ->with([
                'translations' => fn ($q) => $lang ? $q->where('language_id', $lang->id) : $q,
                'featuredImage.variants',
                'terms.translations',
                'author:id,name',
            ])
            ->latest('published_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $items->map(fn ($c) => $this->formatContent($c, $lang)),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/content/{type}/{slug}
     * Returns a single published entry by its slug.
     *
     * Query params:
     *   lang  string  Language code (defaults to site default language)
     */
    public function show(Request $request, string $type, string $slug): JsonResponse
    {
        $contentType = ContentType::where('name', $type)->first();

        if (! $contentType) {
            return response()->json(['error' => 'Content type not found.'], 404);
        }

        $lang = $this->resolveLanguage($request);

        $trans = ContentTranslation::where('slug', $slug)
            ->when($lang, fn ($q) => $q->where('language_id', $lang->id))
            ->whereHas('content', fn ($q) => $q
                ->where('status', 'published')
                ->where('content_type_id', $contentType->id)
            )
            ->with([
                'content.translations',
                'content.featuredImage.variants',
                'content.terms.translations',
                'content.author:id,name',
                'content.fieldValues',
            ])
            ->first();

        if (! $trans) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        return response()->json([
            'data' => $this->formatContent($trans->content, $lang),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function resolveLanguage(Request $request): ?Language
    {
        if ($code = $request->input('lang')) {
            $lang = Language::where('code', $code)->first();
            if ($lang) return $lang;
        }

        return Language::where('is_default', true)->first()
            ?? Language::where('status', '!=', 'disabled')->orderBy('position')->first();
    }

    private function formatContent(Content $content, ?Language $lang): array
    {
        $trans = $lang
            ? $content->translations->firstWhere('language_id', $lang->id) ?? $content->translations->first()
            : $content->translations->first();

        $image = $content->featuredImage ? [
            'url'       => $content->featuredImage->url(),
            'thumbnail' => $content->featuredImage->variantUrl('thumbnail'),
            'medium'    => $content->featuredImage->variantUrl('medium'),
        ] : null;

        $terms = $content->terms->map(function ($term) use ($lang) {
            $t = $lang
                ? $term->translations->firstWhere('language_id', $lang->id) ?? $term->translations->first()
                : $term->translations->first();
            return ['id' => $term->id, 'name' => $t?->name, 'slug' => $t?->slug];
        })->values();

        $data = [
            'id'               => $content->id,
            'status'           => $content->status,
            'published_at'     => $content->published_at?->toIso8601String(),
            'created_at'       => $content->created_at->toIso8601String(),
            'author'           => $content->author
                ? ['id' => $content->author->id, 'name' => $content->author->name]
                : null,
            'title'            => $trans?->title,
            'slug'             => $trans?->slug,
            'excerpt'          => $trans?->excerpt,
            'meta_title'       => $trans?->meta_title,
            'meta_description' => $trans?->meta_description,
            'featured_image'   => $image,
            'terms'            => $terms,
        ];

        return apply_filters('contensio/api/content-item', $data, $content);
    }
}
