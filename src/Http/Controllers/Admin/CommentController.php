<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Admin;

use Contensio\Models\Comment;
use Contensio\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $status    = $request->input('status', 'pending');
        $contentId = $request->input('content_id');
        $search    = $request->input('q');

        $validStatuses = ['pending', 'approved', 'spam', 'trashed', 'all'];
        if (! in_array($status, $validStatuses)) {
            $status = 'pending';
        }

        $query = Comment::with(['content.translations', 'author'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($contentId) {
            $query->where('content_id', $contentId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('body', 'like', "%{$search}%")
                  ->orWhere('author_name', 'like', "%{$search}%")
                  ->orWhere('author_email', 'like', "%{$search}%");
            });
        }

        $comments = $query->paginate(30)->withQueryString();

        // Counts for tab badges
        $counts = Comment::selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $filterContent = $contentId ? Content::with('translations')->find($contentId) : null;

        return view('contensio::admin.comments.index', compact(
            'comments', 'status', 'counts', 'search', 'contentId', 'filterContent'
        ));
    }

    public function approve(int $id)
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => Comment::STATUS_APPROVED]);
        do_action('contensio/comment/approved', $comment->fresh());
        return back()->with('success', 'Comment approved.');
    }

    public function spam(int $id)
    {
        Comment::findOrFail($id)->update(['status' => Comment::STATUS_SPAM]);
        return back()->with('success', 'Comment marked as spam.');
    }

    public function trash(int $id)
    {
        Comment::findOrFail($id)->update(['status' => Comment::STATUS_TRASHED]);
        return back()->with('success', 'Comment moved to trash.');
    }

    public function restore(int $id)
    {
        Comment::findOrFail($id)->update(['status' => Comment::STATUS_PENDING]);
        return back()->with('success', 'Comment restored.');
    }

    public function destroy(int $id)
    {
        Comment::findOrFail($id)->delete();
        do_action('contensio/comment/deleted', $id);
        return back()->with('success', 'Comment deleted.');
    }

    public function bulk(Request $request)
    {
        $action = $request->input('action');
        $ids    = array_filter(array_map('intval', $request->input('ids', [])));

        if (empty($ids)) {
            return back()->with('error', 'No comments selected.');
        }

        match ($action) {
            'spam'    => Comment::whereIn('id', $ids)->update(['status' => Comment::STATUS_SPAM]),
            'trash'   => Comment::whereIn('id', $ids)->update(['status' => Comment::STATUS_TRASHED]),
            'restore' => Comment::whereIn('id', $ids)->update(['status' => Comment::STATUS_PENDING]),
            'approve' => (function () use ($ids) {
                foreach (Comment::whereIn('id', $ids)->get() as $comment) {
                    $comment->update(['status' => Comment::STATUS_APPROVED]);
                    do_action('contensio/comment/approved', $comment);
                }
            })(),
            'delete'  => (function () use ($ids) {
                foreach ($ids as $id) {
                    Comment::find($id)?->delete();
                    do_action('contensio/comment/deleted', $id);
                }
            })(),
            default   => null,
        };

        $count = count($ids);
        return back()->with('success', "{$count} comment(s) updated.");
    }
}
