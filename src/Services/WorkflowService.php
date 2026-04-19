<?php

/**
 * Contensio - The open content platform for Laravel.
 * Content approval workflow — runtime service.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Services;

use Contensio\Models\Content;
use Contensio\Models\Setting;

/**
 * Stateless helpers for the content approval workflow.
 *
 * The "enabled" flag is read from the settings table and cached for the
 * duration of the request (one DB read per page load).
 *
 * Enable the workflow by inserting:
 *   INSERT INTO settings (module, setting_key, value)
 *   VALUES ('workflow', 'enabled', '1');
 *
 * Or via Configuration → Workflow (when that UI is added).
 */
class WorkflowService
{
    private static ?bool $enabled = null;

    // ─── Feature flag ────────────────────────────────────────────────────────

    /**
     * Whether the content approval workflow is globally enabled.
     */
    public static function isEnabled(): bool
    {
        if (self::$enabled !== null) {
            return self::$enabled;
        }

        try {
            return self::$enabled = Setting::where('module', 'workflow')
                ->where('setting_key', 'enabled')
                ->value('value') === '1';
        } catch (\Throwable) {
            return self::$enabled = false;
        }
    }

    /**
     * When true, approving a piece of content automatically publishes it.
     * Default: true. Override by setting workflow.auto_publish = '0'.
     */
    public static function autoPublishOnApproval(): bool
    {
        try {
            $val = Setting::where('module', 'workflow')
                ->where('setting_key', 'auto_publish')
                ->value('value');

            // Default is ON — only disabled when explicitly set to '0'.
            return $val !== '0';
        } catch (\Throwable) {
            return true;
        }
    }

    // ─── Permission helpers ───────────────────────────────────────────────────

    /**
     * User can approve or reject pending content.
     * Granted to: Administrators + anyone with the 'content.approve' permission.
     */
    public static function canApprove($user): bool
    {
        if (! $user) return false;
        return $user->isAdministrator() || $user->hasPermission('content.approve');
    }

    /**
     * User can publish content directly without going through review.
     * Granted to: Administrators + anyone with the 'content.bypass_review' permission.
     */
    public static function canBypassReview($user): bool
    {
        if (! $user) return false;
        return $user->isAdministrator() || $user->hasPermission('content.bypass_review');
    }

    // ─── Counts ──────────────────────────────────────────────────────────────

    /**
     * Number of content items currently awaiting review.
     * Used for the sidebar badge and dashboard widget.
     */
    public static function pendingCount(): int
    {
        if (! self::isEnabled()) {
            return 0;
        }

        try {
            return Content::where('review_status', Content::REVIEW_PENDING)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    // ─── Cache ───────────────────────────────────────────────────────────────

    /**
     * Reset per-request cache (called after toggling the workflow setting).
     */
    public static function flush(): void
    {
        self::$enabled = null;
    }
}
