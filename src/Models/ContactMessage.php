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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactMessage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'extra_data' => 'array',
        'read_at'    => 'datetime',
    ];

    const STATUS_NEW     = 'new';
    const STATUS_READ    = 'read';
    const STATUS_REPLIED = 'replied';
    const STATUS_SPAM    = 'spam';

    public function files(): HasMany
    {
        return $this->hasMany(ContactMessageFile::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(ContactLabel::class, 'contact_message_label', 'message_id', 'label_id')->orderBy('sort_order');
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function markRead(): void
    {
        if (! in_array($this->status, [self::STATUS_REPLIED, self::STATUS_SPAM])) {
            $this->update([
                'status'  => self::STATUS_READ,
                'read_at' => $this->read_at ?? now(),
            ]);
        }
    }
}
