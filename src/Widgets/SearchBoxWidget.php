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

class SearchBoxWidget implements WidgetInterface
{
    public function label(): string       { return 'Search Box'; }
    public function icon(): string        { return 'bi-search'; }
    public function description(): string { return 'A search input form for site content.'; }

    public function configSchema(): array
    {
        return [
            'title'       => ['type' => 'text', 'label' => 'Title',       'default' => ''],
            'placeholder' => ['type' => 'text', 'label' => 'Placeholder', 'default' => 'Search…'],
        ];
    }

    public function render(array $config): string
    {
        return view('contensio::widgets.search-box', compact('config'))->render();
    }
}
