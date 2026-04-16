<?php

/**
 * Contensio - The open content platform for Laravel.
 * Activity log writer — the counterpart to the read-only viewer at /admin/activity-log.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * LICENSE:
 * Permissions of this strongest copyleft license are conditioned on making
 * available complete source code of licensed works and modifications, which
 * include larger works using a licensed work, under the same license.
 * Copyright and license notices must be preserved. Contributors provide an
 * express grant of patent rights. When a modified version is used to provide
 * a service over a network, the complete source code of the modified version
 * must be made available.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Cms\Support;

use Contensio\Cms\Models\ActivityLog;
use Illuminate\Support\Facades\Request;
use Throwable;

/**
 * Lightweight façade for writing to the activity_log table.
 *
 * Usage:
 *
 *     Activity::record('published', 'content', $content->id, 'My new post')
 *             ->withProperties(['title' => $content->title]);
 *
 *     Activity::record('updated', 'role', $role->id)
 *             ->withChanges($oldAttrs, $newAttrs)
 *             ->commit();
 *
 * All writes go through ->commit() at the end (or implicitly on __destruct()).
 *
 * Failure handling: if the DB write fails (e.g. the table doesn't exist during
 * migrate:fresh, or the connection drops), the write is swallowed silently —
 * activity logging should NEVER break the host action.
 */
class Activity
{
    protected array $payload = [];
    protected bool $committed = false;

    public function __construct(string $action, string $subjectType, ?int $subjectId = null, ?string $description = null)
    {
        $this->payload = [
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'description'  => $description,
            'properties'   => null,
            'ip_address'   => Request::ip(),
            'created_at'   => now(),
        ];
    }

    public static function record(string $action, string $subjectType, ?int $subjectId = null, ?string $description = null): self
    {
        return new self($action, $subjectType, $subjectId, $description);
    }

    public function withProperties(array $props): self
    {
        $this->payload['properties'] = json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this;
    }

    /**
     * Convenience: record old → new values on updates. Only keys whose values
     * actually changed are kept — reduces noise.
     */
    public function withChanges(array $before, array $after): self
    {
        $diff = [];
        foreach ($after as $k => $v) {
            $prev = $before[$k] ?? null;
            if ($prev !== $v) {
                $diff[$k] = ['from' => $prev, 'to' => $v];
            }
        }
        if (! empty($diff)) {
            $this->payload['properties'] = json_encode($diff, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return $this;
    }

    public function commit(): void
    {
        if ($this->committed) return;
        $this->committed = true;

        try {
            ActivityLog::create($this->payload);
        } catch (Throwable) {
            // Table may not exist yet during migrations, connection dropped,
            // log table is full, etc. Silently swallow — activity logging
            // must never break the host action.
        }
    }

    public function __destruct()
    {
        if (! $this->committed) {
            $this->commit();
        }
    }
}
