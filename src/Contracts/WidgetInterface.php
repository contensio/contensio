<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Contracts;

interface WidgetInterface
{
    /**
     * Human-readable widget name shown in the admin.
     */
    public function label(): string;

    /**
     * Bootstrap Icon class (e.g. 'bi-newspaper').
     */
    public function icon(): string;

    /**
     * Short description shown below the label in the widget picker.
     */
    public function description(): string;

    /**
     * Configuration schema: defines the fields shown in the "Configure" panel.
     *
     * Each entry is keyed by the config field name. Supported field types:
     *   text       — single-line text input
     *   textarea   — multi-line text input
     *   number     — numeric input (supports 'min', 'max', 'step')
     *   checkbox   — true/false toggle
     *   select     — dropdown ('options' => ['value' => 'Label', ...])
     *
     * Example:
     *   return [
     *       'title' => ['type' => 'text',   'label' => 'Title',  'default' => 'Latest Posts'],
     *       'count' => ['type' => 'number', 'label' => 'Count',  'default' => 5, 'min' => 1, 'max' => 20],
     *   ];
     *
     * Return an empty array if the widget has no configurable options.
     */
    public function configSchema(): array;

    /**
     * Render the widget to an HTML string.
     * $config is the stored instance config, pre-merged with schema defaults.
     */
    public function render(array $config): string;
}
