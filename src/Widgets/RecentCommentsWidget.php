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
use Contensio\Models\Comment;

class RecentCommentsWidget implements WidgetInterface
{
    public function label(): string       { return 'Recent Comments'; }
    public function icon(): string        { return 'bi-chat-left-text'; }
    public function description(): string { return 'Show the latest approved comments across all posts.'; }

    public function configSchema(): array
    {
        return [
            'title' => ['type' => 'text',   'label' => 'Title',            'default' => 'Recent Comments'],
            'count' => ['type' => 'number', 'label' => 'Number to show',   'default' => 5, 'min' => 1, 'max' => 20],
        ];
    }

    public function render(array $config): string
    {
        $comments = Comment::where('status', 'approved')
            ->with('content.translations')
            ->latest()
            ->take((int) $config['count'])
            ->get();

        if ($comments->isEmpty()) {
            return '';
        }

        return view('contensio::widgets.recent-comments', compact('comments', 'config'))->render();
    }
}
