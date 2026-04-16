<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 */

namespace Contensio\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldGroupTranslation extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(FieldGroup::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
