<?php

/**
 * Contensio - The open content platform for Laravel.
 * Admin — Activity log viewer (read-only audit trail).
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

namespace Contensio\Http\Controllers\Admin;

use Contensio\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->integer('user');
        $action  = trim((string) $request->input('action', ''));
        $subject = trim((string) $request->input('subject', ''));
        $from    = $request->input('from');
        $to      = $request->input('to');

        $query = ActivityLog::with('user')
            ->when($user,    fn ($q) => $q->where('user_id', $user))
            ->when($action,  fn ($q) => $q->where('action', $action))
            ->when($subject, fn ($q) => $q->where('subject_type', $subject))
            ->when($from,    fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to,      fn ($q) => $q->where('created_at', '<=', $to . ' 23:59:59'));

        $entries = $query->orderByDesc('id')->paginate(50)->withQueryString();

        // Distinct values for filter dropdowns — pulled from existing data so
        // the list stays relevant even as new action/subject types get added
        $actions  = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $subjects = ActivityLog::select('subject_type')->distinct()->orderBy('subject_type')->pluck('subject_type');
        $users    = \App\Models\User::whereIn('id', ActivityLog::select('user_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('contensio::admin.activity-log.index', compact(
            'entries', 'actions', 'subjects', 'users',
            'user', 'action', 'subject', 'from', 'to'
        ));
    }
}
