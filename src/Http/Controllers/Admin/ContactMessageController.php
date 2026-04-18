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

use Contensio\Models\ContactLabel;
use Contensio\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('q');
        $label  = $request->input('label'); // label slug filter

        $validStatuses = ['all', 'new', 'read', 'replied', 'spam'];
        if (! in_array($status, $validStatuses)) $status = 'all';

        $query = ContactMessage::with(['files', 'labels'])->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($label) {
            $query->whereHas('labels', fn ($q) => $q->where('slug', $label));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->paginate(30)->withQueryString();

        $counts = ContactMessage::selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $allLabels = ContactLabel::orderBy('sort_order')->get();

        return view('contensio::admin.contact.messages.index', compact(
            'messages', 'status', 'counts', 'search', 'label', 'allLabels'
        ));
    }

    public function show(int $id)
    {
        $message   = ContactMessage::with(['files', 'labels'])->findOrFail($id);
        $allLabels = ContactLabel::orderBy('sort_order')->get();
        $message->markRead();

        do_action('contensio/contact/message-read', $message);

        return view('contensio::admin.contact.messages.show', compact('message', 'allLabels'));
    }

    public function destroy(int $id)
    {
        $message = ContactMessage::findOrFail($id);

        // Delete uploaded files from storage
        foreach ($message->files as $file) {
            try {
                \Illuminate\Support\Facades\Storage::disk($file->disk)->delete($file->file_path);
            } catch (\Throwable) {}
        }

        $message->delete();
        do_action('contensio/contact/message-deleted', $id);

        return back()->with('success', 'Message deleted.');
    }

    public function bulk(Request $request)
    {
        $action = $request->input('action');
        $ids    = array_filter(array_map('intval', $request->input('ids', [])));

        if (empty($ids)) {
            return back()->with('error', 'No messages selected.');
        }

        match ($action) {
            'mark_read'    => ContactMessage::whereIn('id', $ids)->update(['status' => ContactMessage::STATUS_READ, 'read_at' => now()]),
            'mark_replied' => ContactMessage::whereIn('id', $ids)->update(['status' => ContactMessage::STATUS_REPLIED]),
            'mark_spam'    => ContactMessage::whereIn('id', $ids)->update(['status' => ContactMessage::STATUS_SPAM]),
            'delete'       => (function () use ($ids) {
                foreach (ContactMessage::with('files')->whereIn('id', $ids)->get() as $message) {
                    foreach ($message->files as $file) {
                        try { \Illuminate\Support\Facades\Storage::disk($file->disk)->delete($file->file_path); } catch (\Throwable) {}
                    }
                    $message->delete();
                }
            })(),
            default => null,
        };

        return back()->with('success', count($ids) . ' message(s) updated.');
    }

    public function export(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = ContactMessage::orderByDesc('created_at');
        if ($status !== 'all') $query->where('status', $status);

        $messages = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="contact-messages-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($messages) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

            fputcsv($handle, ['ID', 'Status', 'Name', 'Email', 'Subject', 'Message', 'Date']);

            foreach ($messages as $msg) {
                fputcsv($handle, [
                    $msg->id,
                    $msg->status,
                    $msg->name,
                    $msg->email,
                    $msg->subject,
                    $msg->message,
                    $msg->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
