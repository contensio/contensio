<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Widgets;

use Contensio\Contracts\WidgetInterface;
use Contensio\Models\Taxonomy;

class CategoriesWidget implements WidgetInterface
{
    public function label(): string       { return 'Categories'; }
    public function icon(): string        { return 'bi-folder'; }
    public function description(): string { return 'List all post categories with post counts.'; }

    public function configSchema(): array
    {
        return [
            'title'      => ['type' => 'text',     'label' => 'Title',             'default' => 'Categories'],
            'show_count' => ['type' => 'checkbox', 'label' => 'Show post count',   'default' => true],
            'hide_empty' => ['type' => 'checkbox', 'label' => 'Hide empty categories', 'default' => true],
        ];
    }

    public function render(array $config): string
    {
        $taxonomy = Taxonomy::where('slug', 'category')->first();
        if (! $taxonomy) {
            return '';
        }

        $categories = $taxonomy->terms()
            ->with('translations')
            ->withCount(['contents' => function ($q) {
                $q->where('status', 'published');
            }])
            ->get();

        if ($config['hide_empty']) {
            $categories = $categories->filter(fn ($t) => $t->contents_count > 0);
        }

        if ($categories->isEmpty()) {
            return '';
        }

        return view('contensio::widgets.categories', compact('categories', 'config'))->render();
    }
}
