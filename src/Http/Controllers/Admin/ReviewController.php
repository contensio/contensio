<?php

/**
 * Contensio - The open content platform for Laravel.
 * Content approval workflow — review queue controller.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Http\Controllers\Admin;

use Contensio\Mail\ContentReviewedNotification;
use Contensio\Mail\ContentSubmittedNotification;
use Contensio\Models\Content;
use Contensio\Models\ContentReviewLog;
use Contensio\Services\WorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Pending review queue — all content awaiting a decision.
     */
    public function index(): View
    {
        $items = Content::where('review_status', Content::REVIEW_PENDING)
            ->with(['translations', 'author', 'contentType'])
            ->latest('review_requested_at')
            ->get();

        return view('contensio::admin.reviews.index', compact('items'));
    }

    /**
     * Approve a pending content item.
     *
     * When auto_publish is enabled (default), the content is published immediately.
     * The author receives an email notification.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        abort_unless(WorkflowService::canApprove(auth()->user()), 403);

        $content = Content::with(['translations', 'author'])->findOrFail($id);

        if ($content->review_status !== Content::REVIEW_PENDING) {
            return back()->with('error', 'This content is not pending review.');
        }

        $publishNow = WorkflowService::autoPublishOnApproval();

        $content->update([
            'review_status'  => Content::REVIEW_APPROVED,
            'review_notes'   => null,
            'reviewed_by_id' => auth()->id(),
            'reviewed_at'    => now(),
            // Auto-publish when enabled — keep existing published_at if already set.
            'status'         => $publishNow ? Content::STATUS_PUBLISHED : $content->status,
            'published_at'   => $publishNow
                ? ($content->published_at ?? now())
                : $content->published_at,
        ]);

        ContentReviewLog::create([
            'content_id' => $content->id,
            'user_id'    => auth()->id(),
            'action'     => 'approved',
            'notes'      => null,
            'created_at' => now(),
        ]);

        // Notify the author.
        if ($content->author?->email) {
            Mail::to($content->author)
                ->send(new ContentReviewedNotification($content, 'approved', null));
        }

        $title = $content->translations->first()?->title ?? 'Untitled';

        return back()->with('success', "\"{$title}\" approved" . ($publishNow ? ' and published.' : '.'));
    }

    /**
     * Reject a pending content item — soft (author can revise) or hard (permanent).
     *
     * Request fields:
     *   notes       string  required  Reason shown to the author.
     *   reject_type string  required  soft_rejected | hard_rejected
     */
    public function reject(Request $request, int $id): RedirectResponse
    {
        abort_unless(WorkflowService::canApprove(auth()->user()), 403);

        $request->validate([
            'notes'       => ['required', 'string', 'max:1000'],
            'reject_type' => ['required', 'in:soft_rejected,hard_rejected'],
        ]);

        $content = Content::with(['translations', 'author'])->findOrFail($id);

        if ($content->review_status !== Content::REVIEW_PENDING) {
            return back()->with('error', 'This content is not pending review.');
        }

        $reviewStatus = $request->reject_type;

        $content->update([
            'review_status'  => $reviewStatus,
            'review_notes'   => $request->notes,
            'reviewed_by_id' => auth()->id(),
            'reviewed_at'    => now(),
        ]);

        ContentReviewLog::create([
            'content_id' => $content->id,
            'user_id'    => auth()->id(),
            'action'     => $reviewStatus,
            'notes'      => $request->notes,
            'created_at' => now(),
        ]);

        // Notify the author.
        if ($content->author?->email) {
            Mail::to($content->author)
                ->send(new ContentReviewedNotification($content, $reviewStatus, $request->notes));
        }

        $title  = $content->translations->first()?->title ?? 'Untitled';
        $label  = $reviewStatus === Content::REVIEW_SOFT_REJECTED
            ? 'returned for revision'
            : 'permanently rejected';

        return back()->with('success', "\"{$title}\" {$label}.");
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Build the edit URL for any content item regardless of type.
     */
    public static function editUrl(Content $content): string
    {
        $type = $content->contentType?->name ?? 'page';

        return match ($type) {
            'page'  => route('contensio.account.pages.edit', $content->id),
            'post'  => route('contensio.account.posts.edit', $content->id),
            default => route('contensio.account.content.edit', [$type, $content->id]),
        };
    }

    /**
     * Notify all users with content.approve permission that new content
     * is awaiting review. Called from ContentController after submit.
     */
    public static function notifyReviewers(Content $content): void
    {
        try {
            $approvers = \App\Models\User::whereHas('roles', fn ($q) =>
                $q->whereHas('permissions', fn ($q) =>
                    $q->where('name', 'content.approve')
                )
            )->orWhereHas('roles', fn ($q) =>
                $q->whereIn('name', ['super_admin', 'admin'])
            )->get()->unique('id');

            foreach ($approvers as $approver) {
                if ($approver->email) {
                    Mail::to($approver)->send(new ContentSubmittedNotification($content, auth()->user()));
                }
            }
        } catch (\Throwable) {
            // Never let a mail failure block a content save.
        }
    }
}
