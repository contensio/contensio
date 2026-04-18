<?php

/**
 * Contensio - The open content platform for Laravel.
 * Admin — Tools / Backups.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Admin\Tools;

use Contensio\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class BackupController extends Controller
{
    public function __construct(protected BackupService $backups) {}

    // -------------------------------------------------------------------------
    // List
    // -------------------------------------------------------------------------

    public function index()
    {
        $backups = $this->backups->list();

        return view('contensio::admin.tools.backups', compact('backups'));
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $includeFiles = (bool) $request->input('include_files', true);

        try {
            $this->backups->create($includeFiles);
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'Backup failed: ' . $e->getMessage()]);
        }

        return back()->with('success', __('contensio::admin.backup.created'));
    }

    // -------------------------------------------------------------------------
    // Download
    // -------------------------------------------------------------------------

    public function download(string $filename)
    {
        try {
            $path = $this->backups->resolveBackupPath($filename);
        } catch (\InvalidArgumentException) {
            abort(404);
        }

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function destroy(string $filename)
    {
        try {
            $this->backups->delete($filename);
        } catch (\InvalidArgumentException) {
            abort(404);
        }

        return back()->with('success', __('contensio::admin.backup.deleted'));
    }

    // -------------------------------------------------------------------------
    // Restore — step 1: upload & validate
    // -------------------------------------------------------------------------

    public function restoreUpload(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:524288', // 512 MB
        ]);

        $uploaded = $request->file('backup_file');
        $tmpPath  = sys_get_temp_dir() . '/contensio-restore-upload-' . uniqid() . '.zip';
        $uploaded->move(dirname($tmpPath), basename($tmpPath));

        try {
            $manifest = $this->backups->validate($tmpPath);
        } catch (\RuntimeException $e) {
            @unlink($tmpPath);
            return back()->withErrors(['backup_file' => $e->getMessage()]);
        }

        // Store the temp path in session so step 2 can find it
        session(['contensio_restore_tmp' => $tmpPath]);

        return redirect()->route('contensio.account.tools.backups.restore-confirm')
            ->with('manifest', $manifest);
    }

    // -------------------------------------------------------------------------
    // Restore — step 2: confirmation + password
    // -------------------------------------------------------------------------

    public function restoreConfirm(Request $request)
    {
        $tmpPath = session('contensio_restore_tmp');

        if (! $tmpPath || ! file_exists($tmpPath)) {
            return redirect()->route('contensio.account.tools.backups')
                ->withErrors(['backup' => __('contensio::admin.backup.restore_session_expired')]);
        }

        // Re-read manifest from the stored zip (session flash may have expired on redirect)
        try {
            $manifest = $this->backups->validate($tmpPath);
        } catch (\RuntimeException $e) {
            @unlink($tmpPath);
            session()->forget('contensio_restore_tmp');
            return redirect()->route('contensio.account.tools.backups')
                ->withErrors(['backup' => $e->getMessage()]);
        }

        return view('contensio::admin.tools.backup-confirm', compact('manifest'));
    }

    // -------------------------------------------------------------------------
    // Restore — step 3: execute
    // -------------------------------------------------------------------------

    public function restoreExecute(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // Verify admin password
        if (! Hash::check($request->input('password'), auth()->user()->password)) {
            return back()->withErrors(['password' => __('contensio::admin.backup.wrong_password')]);
        }

        $tmpPath = session('contensio_restore_tmp');

        if (! $tmpPath || ! file_exists($tmpPath)) {
            return redirect()->route('contensio.account.tools.backups')
                ->withErrors(['backup' => __('contensio::admin.backup.restore_session_expired')]);
        }

        try {
            $result = $this->backups->restore($tmpPath);
        } catch (\Throwable $e) {
            return back()->withErrors(['backup' => 'Restore failed: ' . $e->getMessage()]);
        } finally {
            @unlink($tmpPath);
            session()->forget('contensio_restore_tmp');
        }

        return redirect()->route('contensio.account.tools.backups')
            ->with('success', __('contensio::admin.backup.restored', [
                'tables' => $result['tables'],
                'files'  => $result['files'],
            ]));
    }
}
