<?php

/**
 * Contensio - The open content platform for Laravel.
 * Admin — Tools / Import & Export.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * LICENSE:
 * Permissions of this strongest copyleft license are conditioned on making
 * available complete source code of licensed works and modifications, which
 * include larger works using a licensed work, under the same license.
 * Copyright and license notices must be preserved. Contributors provide an
 * express grant of patent rights. When a modified version is used to provide
 * a service over a network, the complete source code of the modified version
 * must be made available.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Cms\Http\Controllers\Admin\Tools;

use Contensio\Cms\Models\Content;
use Contensio\Cms\Models\ContentMeta;
use Contensio\Cms\Models\ContentTranslation;
use Contensio\Cms\Models\ContentType;
use Contensio\Cms\Models\Language;
use Contensio\Cms\Models\Menu;
use Contensio\Cms\Models\MenuItem;
use Contensio\Cms\Models\MenuItemTranslation;
use Contensio\Cms\Models\MenuTranslation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportExportController extends Controller
{
    public const FORMAT_VERSION = '1.0';

    public function index()
    {
        $stats = [
            'pages' => Content::whereHas('contentType', fn ($q) => $q->where('name', 'page'))->count(),
            'posts' => Content::whereHas('contentType', fn ($q) => $q->where('name', 'post'))->count(),
            'menus' => Menu::count(),
        ];

        return view('cms::admin.tools.import-export', compact('stats'));
    }

    public function export(Request $request)
    {
        $includeContent = (bool) $request->input('include_content', true);
        $includeMenus   = (bool) $request->input('include_menus', true);

        $payload = [
            '_format' => [
                'product'     => 'contensio',
                'version'     => self::FORMAT_VERSION,
                'exported_at' => now()->toIso8601String(),
                'source'      => config('app.url'),
            ],
            'languages' => Language::orderBy('id')
                ->get(['code', 'name', 'is_default', 'status'])
                ->map(fn ($l) => [
                    'code'       => $l->code,
                    'name'       => $l->name,
                    'is_default' => (bool) $l->is_default,
                    'status'     => $l->status ?? 'active',
                ])
                ->values()
                ->all(),
        ];

        if ($includeContent) {
            $payload['contents'] = $this->exportContents();
        }

        if ($includeMenus) {
            $payload['menus'] = $this->exportMenus();
        }

        $filename = sprintf(
            'contensio-export-%s-%s.json',
            Str::slug(parse_url(config('app.url'), PHP_URL_HOST) ?? 'site'),
            now()->format('Ymd-His')
        );

        return response()->json($payload, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|mimes:json,txt|max:20480',
            'conflict' => 'required|in:skip,overwrite',
        ]);

        $raw     = file_get_contents($request->file('file')->getRealPath());
        $payload = json_decode($raw, true);

        if (! is_array($payload) || ($payload['_format']['product'] ?? null) !== 'contensio') {
            return back()->withErrors(['file' => 'Not a valid Contensio export file.']);
        }

        $conflict = $request->input('conflict');
        $stats    = ['languages' => 0, 'contents' => 0, 'menus' => 0, 'skipped' => 0];

        DB::transaction(function () use ($payload, $conflict, &$stats) {
            // Languages first — contents + menus depend on them
            foreach ($payload['languages'] ?? [] as $lang) {
                if (empty($lang['code'])) continue;
                $existing = Language::where('code', $lang['code'])->first();
                if ($existing && $conflict === 'skip') { $stats['skipped']++; continue; }
                Language::updateOrCreate(
                    ['code' => $lang['code']],
                    [
                        'name'       => $lang['name'] ?? $lang['code'],
                        'is_default' => $existing ? $existing->is_default : (bool) ($lang['is_default'] ?? false),
                        'status'     => $lang['status'] ?? 'active',
                    ]
                );
                $stats['languages']++;
            }

            $langMap = Language::pluck('id', 'code')->all();

            foreach ($payload['contents'] ?? [] as $item) {
                $imported = $this->importContent($item, $langMap, $conflict);
                $stats[$imported ? 'contents' : 'skipped']++;
            }

            foreach ($payload['menus'] ?? [] as $menu) {
                $imported = $this->importMenu($menu, $langMap, $conflict);
                $stats[$imported ? 'menus' : 'skipped']++;
            }
        });

        return back()->with('success', sprintf(
            'Import complete — %d contents, %d menus, %d languages, %d skipped.',
            $stats['contents'], $stats['menus'], $stats['languages'], $stats['skipped']
        ));
    }

    /* ── Export helpers ─────────────────────────────────────────────── */

    protected function exportContents(): array
    {
        return Content::with(['contentType', 'translations.language', 'meta.language'])
            ->whereHas('contentType', fn ($q) => $q->whereIn('name', ['page', 'post']))
            ->orderBy('id')
            ->get()
            ->map(function (Content $c) {
                return [
                    'type'           => $c->contentType->name,
                    'status'         => $c->status,
                    'position'       => $c->position,
                    'allow_comments' => (bool) $c->allow_comments,
                    'published_at'   => optional($c->published_at)->toIso8601String(),
                    'blocks'         => $c->blocks ?? [],
                    'translations'   => $c->translations->map(fn (ContentTranslation $t) => [
                        'language'         => $t->language?->code,
                        'title'            => $t->title,
                        'slug'             => $t->slug,
                        'body'             => $t->body,
                        'excerpt'          => $t->excerpt,
                        'meta_title'       => $t->meta_title,
                        'meta_description' => $t->meta_description,
                        'og_image'         => $t->og_image,
                    ])->values()->all(),
                    'meta' => $c->meta->map(fn (ContentMeta $m) => [
                        'key'      => $m->meta_key,
                        'value'    => $m->meta_value,
                        'language' => $m->language?->code,
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    protected function exportMenus(): array
    {
        return Menu::with(['translations.language', 'items.translations.language'])
            ->orderBy('id')
            ->get()
            ->map(function (Menu $m) {
                return [
                    'name'         => $m->name,
                    'translations' => $m->translations->map(fn (MenuTranslation $t) => [
                        'language' => $t->language?->code,
                        'label'    => $t->label,
                    ])->values()->all(),
                    'items' => $m->items->sortBy('position')->values()->map(fn (MenuItem $i) => [
                        'type'         => $i->type,
                        'target'       => $i->target,
                        'icon'         => $i->icon,
                        'is_active'    => (bool) $i->is_active,
                        'position'     => $i->position,
                        'parent_index' => null, // flat for v1 — nesting support later
                        'translations' => $i->translations->map(fn (MenuItemTranslation $t) => [
                            'language' => $t->language?->code,
                            'label'    => $t->label,
                            'url'      => $t->url,
                        ])->values()->all(),
                    ])->all(),
                ];
            })
            ->values()
            ->all();
    }

    /* ── Import helpers ─────────────────────────────────────────────── */

    protected function importContent(array $item, array $langMap, string $conflict): bool
    {
        $typeName = $item['type'] ?? null;
        if (! in_array($typeName, ['page', 'post'], true)) return false;

        $contentType = ContentType::where('name', $typeName)->first();
        if (! $contentType) return false;

        // Match by default-language slug
        $defaultSlug = collect($item['translations'] ?? [])->firstWhere('language', $this->defaultLangCode())['slug']
            ?? ($item['translations'][0]['slug'] ?? null);

        $existing = null;
        if ($defaultSlug) {
            $existing = Content::where('content_type_id', $contentType->id)
                ->whereHas('translations', fn ($q) => $q->where('slug', $defaultSlug))
                ->first();
        }
        if ($existing && $conflict === 'skip') return false;

        $content = $existing ?: new Content();
        $content->content_type_id = $contentType->id;
        $content->code            = $content->code ?: Str::random(16);
        $content->status          = $item['status'] ?? Content::STATUS_DRAFT;
        $content->position        = (int) ($item['position'] ?? 0);
        $content->allow_comments  = (bool) ($item['allow_comments'] ?? false);
        $content->published_at    = $item['published_at'] ?? null;
        $content->blocks          = $item['blocks'] ?? [];
        $content->save();

        // Replace translations + meta (cleanest on overwrite)
        $content->translations()->delete();
        foreach (($item['translations'] ?? []) as $t) {
            $langId = $langMap[$t['language'] ?? ''] ?? null;
            if (! $langId) continue;
            $content->translations()->create([
                'language_id'      => $langId,
                'title'            => $t['title'] ?? '',
                'slug'             => $t['slug'] ?? Str::slug($t['title'] ?? 'untitled'),
                'body'             => $t['body'] ?? null,
                'excerpt'          => $t['excerpt'] ?? null,
                'meta_title'       => $t['meta_title'] ?? null,
                'meta_description' => $t['meta_description'] ?? null,
                'og_image'         => $t['og_image'] ?? null,
            ]);
        }

        $content->meta()->delete();
        foreach (($item['meta'] ?? []) as $m) {
            $content->meta()->create([
                'meta_key'    => $m['key'] ?? '',
                'meta_value'  => $m['value'] ?? null,
                'language_id' => $langMap[$m['language'] ?? ''] ?? null,
            ]);
        }

        return true;
    }

    protected function importMenu(array $item, array $langMap, string $conflict): bool
    {
        if (empty($item['name'])) return false;

        $existing = Menu::where('name', $item['name'])->first();
        if ($existing && $conflict === 'skip') return false;

        $menu = $existing ?: new Menu();
        $menu->name = $item['name'];
        $menu->save();

        $menu->translations()->delete();
        foreach (($item['translations'] ?? []) as $t) {
            $langId = $langMap[$t['language'] ?? ''] ?? null;
            if (! $langId) continue;
            $menu->translations()->create([
                'language_id' => $langId,
                'label'       => $t['label'] ?? $menu->name,
            ]);
        }

        $menu->items()->delete();
        foreach (($item['items'] ?? []) as $entry) {
            $mi = $menu->items()->create([
                'parent_id'    => null,
                'type'         => $entry['type']      ?? 'custom_url',
                'reference_id' => null, // content refs are best-effort; import v1 skips to avoid mis-links
                'target'       => $entry['target']    ?? '_self',
                'icon'         => $entry['icon']      ?? null,
                'is_active'    => (bool) ($entry['is_active'] ?? true),
                'position'     => (int)  ($entry['position']  ?? 0),
            ]);
            foreach (($entry['translations'] ?? []) as $t) {
                $langId = $langMap[$t['language'] ?? ''] ?? null;
                if (! $langId) continue;
                $mi->translations()->create([
                    'language_id' => $langId,
                    'label'       => $t['label'] ?? '',
                    'url'         => $t['url']   ?? null,
                ]);
            }
        }

        return true;
    }

    protected function defaultLangCode(): ?string
    {
        return Language::where('is_default', true)->value('code');
    }
}
