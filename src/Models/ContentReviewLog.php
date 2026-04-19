<?php

/**
 * Contensio - The open content platform for Laravel.
 * Append-only audit trail for content review actions.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentReviewLog extends Model
{
    /**
     * This table is append-only — no updated_at column.
     */
    const UPDATED_AT = null;

    protected $table  = 'content_review_log';
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
