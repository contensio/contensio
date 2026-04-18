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
use Illuminate\Support\Str;

class ContactLabelController extends Controller
{
    // -------------------------------------------------------------------------
    // Labels management page
    // -------------------------------------------------------------------------

    public function index()
    {
        $labels = ContactLabel::withCount('messages')->orderBy('sort_order')->get();

        return view('contensio::admin.contact.labels.index', compact('labels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        $slug = $this->uniqueSlug(Str::slug($request->name));

        $maxOrder = ContactLabel::max('sort_order') ?? 0;

        ContactLabel::create([
            'name'       => $request->name,
            'color'      => $request->color,
            'slug'       => $slug,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', __('contensio::admin.contact.labels.created'));
    }

    public function update(Request $request, int $id)
    {
        $label = ContactLabel::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        $label->update([
            'name'  => $request->name,
            'color' => $request->color,
        ]);

        return back()->with('success', __('contensio::admin.contact.labels.updated'));
    }

    public function destroy(int $id)
    {
        ContactLabel::findOrFail($id)->delete();

        return back()->with('success', __('contensio::admin.contact.labels.deleted'));
    }

    // -------------------------------------------------------------------------
    // Attach / detach labels on a message (JSON, called via Alpine fetch)
    // -------------------------------------------------------------------------

    public function attachToMessage(Request $request, int $messageId)
    {
        $request->validate(['label_id' => 'required|exists:contact_labels,id']);

        $message = ContactMessage::findOrFail($messageId);
        $message->labels()->syncWithoutDetaching([$request->label_id]);

        $label = ContactLabel::find($request->label_id);

        return response()->json([
            'id'    => $label->id,
            'name'  => $label->name,
            'color' => $label->color,
        ]);
    }

    public function detachFromMessage(int $messageId, int $labelId)
    {
        $message = ContactMessage::findOrFail($messageId);
        $message->labels()->detach($labelId);

        return response()->json(['ok' => true]);
    }

    // -------------------------------------------------------------------------
    // Bulk assign label (used from messages inbox bulk form)
    // -------------------------------------------------------------------------

    public function bulkAssign(Request $request)
    {
        $request->validate(['label_id' => 'required|exists:contact_labels,id']);

        $ids = array_filter(array_map('intval', $request->input('ids', [])));

        if (empty($ids)) {
            return back()->with('error', 'No messages selected.');
        }

        $messages = ContactMessage::whereIn('id', $ids)->get();
        foreach ($messages as $message) {
            $message->labels()->syncWithoutDetaching([$request->label_id]);
        }

        return back()->with('success', count($ids) . ' message(s) labelled.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: 'label';
        $i = 0;

        while (ContactLabel::where('slug', $slug)->exists()) {
            $slug = $base . '-' . ++$i;
        }

        return $slug;
    }
}
