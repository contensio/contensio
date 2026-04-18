<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactField extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_default'  => 'boolean',
        'required'    => 'boolean',
        'options'     => 'array',
        'conditional' => 'array',
    ];

    /** Field types that support choices (options array). */
    public const CHOICE_TYPES = ['select', 'multiselect'];

    /** Default system fields — seeded on first contact page load. */
    public const DEFAULTS = [
        ['key' => 'name',    'type' => 'text',     'required' => true,  'sort_order' => 10],
        ['key' => 'email',   'type' => 'email',    'required' => true,  'sort_order' => 20],
        ['key' => 'subject', 'type' => 'text',     'required' => false, 'sort_order' => 30],
        ['key' => 'message', 'type' => 'textarea', 'required' => true,  'sort_order' => 40],
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ContactFieldTranslation::class);
    }

    /**
     * Translation for the given language (falls back to first available).
     */
    public function translationFor(?int $languageId): ?ContactFieldTranslation
    {
        if ($languageId) {
            $t = $this->translations->firstWhere('language_id', $languageId);
            if ($t) return $t;
        }

        return $this->translations->first();
    }

    /**
     * Ensure default fields exist in DB. Idempotent — safe to call on every boot.
     */
    public static function seedDefaults(): void
    {
        foreach (static::DEFAULTS as $def) {
            if (! static::where('key', $def['key'])->exists()) {
                static::create(array_merge($def, ['is_default' => true]));
            }
        }
    }
}
