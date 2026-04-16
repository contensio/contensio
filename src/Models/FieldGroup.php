<?php

/**
 * Contensio - The open content platform for Laravel.
 * A flexible content foundation for blogs, shops, communities,
 * and any content-driven app.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class FieldGroup extends Model
{
    protected $guarded = [];

    /** Fields that belong to this group, ordered by display position. */
    public function fields(): HasMany
    {
        return $this->hasMany(Field::class)->orderBy('position');
    }

    /** Per-language overrides for label + description. */
    public function translations(): HasMany
    {
        return $this->hasMany(FieldGroupTranslation::class);
    }

    /** Content types this group is attached to (polymorphic pivot). */
    public function contentTypes(): MorphToMany
    {
        return $this->morphedByMany(
            ContentType::class,
            'attachable',
            'field_group_attachments'
        )->withPivot('position')->withTimestamps();
    }
}
