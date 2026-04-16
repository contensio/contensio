<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    /** v1 types — extend by adding to config/cms.php or via a plugin in future. */
    public const TYPES = [
        'text',
        'textarea',
        'rich-text',
        'number',
        'boolean',
        'date',
        'select',
        'multi-select',
        'media',
        'url',
    ];

    protected $guarded = [];

    protected $casts = [
        'config'           => 'array',
        'visibility_rules' => 'array',
        'is_translatable'  => 'boolean',
        'is_required'      => 'boolean',
        'position'         => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(FieldGroup::class, 'field_group_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FieldTranslation::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ContentFieldValue::class, 'field_id');
    }
}
