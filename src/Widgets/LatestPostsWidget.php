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
use Contensio\Models\Content;
use Contensio\Models\ContentType;

class LatestPostsWidget implements WidgetInterface
{
    public function label(): string       { return 'Latest Posts'; }
    public function icon(): string        { return 'bi-newspaper'; }
    public function description(): string { return 'Show the most recently published posts.'; }

    public function configSchema(): array
    {
        return [
            'title'        => ['type' => 'text',     'label' => 'Title',              'default' => 'Latest Posts'],
            'count'        => ['type' => 'number',   'label' => 'Number of posts',    'default' => 5,     'min' => 1, 'max' => 20],
            'show_date'    => ['type' => 'checkbox', 'label' => 'Show published date', 'default' => true],
            'show_excerpt' => ['type' => 'checkbox', 'label' => 'Show excerpt',        'default' => false],
            'show_image'   => ['type' => 'checkbox', 'label' => 'Show featured image', 'default' => false],
        ];
    }

    public function render(array $config): string
    {
        $postType = ContentType::where('name', 'post')->first();
        if (! $postType) {
            return '';
        }

        $posts = Content::where('content_type_id', $postType->id)
            ->where('status', 'published')
            ->with(['translations', 'featuredImage'])
            ->latest('published_at')
            ->take((int) $config['count'])
            ->get();

        if ($posts->isEmpty()) {
            return '';
        }

        return view('contensio::widgets.latest-posts', compact('posts', 'config'))->render();
    }
}
