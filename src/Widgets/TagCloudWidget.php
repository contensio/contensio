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

class TagCloudWidget implements WidgetInterface
{
    public function label(): string       { return 'Tag Cloud'; }
    public function icon(): string        { return 'bi-tags'; }
    public function description(): string { return 'Display tags as a cloud, sized by usage frequency.'; }

    public function configSchema(): array
    {
        return [
            'title' => ['type' => 'text',   'label' => 'Title',          'default' => 'Tags'],
            'limit' => ['type' => 'number', 'label' => 'Max tags shown', 'default' => 30, 'min' => 5, 'max' => 100],
        ];
    }

    public function render(array $config): string
    {
        $taxonomy = Taxonomy::where('slug', 'tag')->first();
        if (! $taxonomy) {
            return '';
        }

        $tags = $taxonomy->terms()
            ->with('translations')
            ->withCount(['contents' => fn ($q) => $q->where('status', 'published')])
            ->having('contents_count', '>', 0)
            ->orderByDesc('contents_count')
            ->take((int) $config['limit'])
            ->get();

        if ($tags->isEmpty()) {
            return '';
        }

        $min = $tags->min('contents_count');
        $max = $tags->max('contents_count');

        return view('contensio::widgets.tag-cloud', compact('tags', 'config', 'min', 'max'))->render();
    }
}
