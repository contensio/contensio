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

class CustomHtmlWidget implements WidgetInterface
{
    public function label(): string       { return 'Custom HTML'; }
    public function icon(): string        { return 'bi-code-slash'; }
    public function description(): string { return 'Output arbitrary HTML — ads, embeds, or any custom markup.'; }

    public function configSchema(): array
    {
        return [
            'title'   => ['type' => 'text',     'label' => 'Title (optional)', 'default' => ''],
            'content' => ['type' => 'textarea', 'label' => 'HTML content',     'default' => '', 'rows' => 8, 'monospace' => true],
        ];
    }

    public function render(array $config): string
    {
        if (empty(trim($config['content'] ?? ''))) {
            return '';
        }
        return view('contensio::widgets.custom-html', compact('config'))->render();
    }
}
