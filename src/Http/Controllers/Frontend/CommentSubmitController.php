<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Frontend;

use Contensio\Models\Comment;
use Contensio\Models\Content;
use Contensio\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class CommentSubmitController extends Controller
{
    public function store(Request $request)
    {
        // Global comments enabled?
        $enabled = Setting::where('module', 'comments')
            ->where('setting_key', 'comments_enabled')
            ->value('value');

        if (! $enabled) {
            return back()->with('comment_error', 'Comments are disabled.');
        }

        $rules = [
            'content_id' => 'required|integer|exists:contents,id',
            'parent_id'  => 'nullable|integer|exists:comments,id',
            'body'       => 'required|string|max:5000',
        ];

        if (! auth()->check()) {
            $rules['author_name']  = 'required|string|max:200';
            $rules['author_email'] = 'required|email|max:200';
        }

        $request->validate($rules);

        $content = Content::find($request->content_id);

        if (! $content || ! $content->allow_comments) {
            return back()->with('comment_error', 'Comments are not allowed on this post.');
        }

        // Guest comments allowed?
        $allowGuests = Setting::where('module', 'comments')
            ->where('setting_key', 'comments_allow_guests')
            ->value('value');

        if (! auth()->check() && ! $allowGuests) {
            return back()->with('comment_error', 'You must be logged in to comment.');
        }

        // Auto-close check
        $closeAfterDays = (int) (Setting::where('module', 'comments')
            ->where('setting_key', 'comments_close_after_days')
            ->value('value') ?? 0);

        if ($closeAfterDays > 0 && $content->published_at) {
            $daysSincePublish = $content->published_at->diffInDays(now());
            if ($daysSincePublish > $closeAfterDays) {
                return back()->with('comment_error', 'Comments are closed for this post.');
            }
        }

        $requireApproval = Setting::where('module', 'comments')
            ->where('setting_key', 'comments_require_approval')
            ->value('value');

        $status = $requireApproval ? Comment::STATUS_PENDING : Comment::STATUS_APPROVED;

        Comment::create([
            'code'         => Str::random(16),
            'content_id'   => $content->id,
            'parent_id'    => $request->filled('parent_id') ? (int) $request->parent_id : null,
            'author_id'    => auth()->id(),
            'author_name'  => auth()->check() ? null : $request->author_name,
            'author_email' => auth()->check() ? null : $request->author_email,
            'body'         => $request->body,
            'status'       => $status,
        ]);

        $message = $status === Comment::STATUS_PENDING
            ? 'Your comment has been submitted and is awaiting moderation.'
            : 'Your comment has been posted.';

        return back()->with('comment_success', $message);
    }
}
