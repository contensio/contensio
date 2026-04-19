<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetInstance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'config'    => 'array',
        'is_active' => 'boolean',
        'position'  => 'integer',
    ];
}
