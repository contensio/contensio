<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Middleware;

use Closure;
use Contensio\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        try {
            $settings = Setting::where('module', 'users')
                ->whereIn('setting_key', ['inactivity_logout_days', 'max_sessions'])
                ->pluck('value', 'setting_key');

            // ── 1. Inactivity check ──────────────────────────────────────────
            $inactivityDays = intval($settings['inactivity_logout_days'] ?? 0);
            if ($inactivityDays > 0 && $user->last_active_at) {
                if ($user->last_active_at->lt(now()->subDays($inactivityDays))) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('contensio.login')
                        ->with('status', 'You were signed out due to inactivity.');
                }
            }

            // ── 2. Update last_active_at (throttled: once per minute) ────────
            if (! $user->last_active_at || $user->last_active_at->lt(now()->subMinute())) {
                DB::table('users')->where('id', $user->id)->update(['last_active_at' => now()]);
            }

            // ── 3. Max active sessions enforcement ───────────────────────────
            $maxSessions = intval($settings['max_sessions'] ?? 0);
            if ($maxSessions > 0) {
                $sessionId = $request->session()->getId();

                DB::table('user_sessions')->upsert(
                    [
                        'user_id'        => $user->id,
                        'session_id'     => $sessionId,
                        'ip_address'     => $request->ip(),
                        'user_agent'     => mb_substr($request->userAgent() ?? '', 0, 500),
                        'last_active_at' => now()->toDateTimeString(),
                        'created_at'     => now()->toDateTimeString(),
                    ],
                    ['user_id', 'session_id'],
                    ['last_active_at', 'ip_address', 'user_agent']
                );

                // Keep only the N most recently active sessions; delete the rest
                $keep = DB::table('user_sessions')
                    ->where('user_id', $user->id)
                    ->orderByDesc('last_active_at')
                    ->limit($maxSessions)
                    ->pluck('session_id');

                DB::table('user_sessions')
                    ->where('user_id', $user->id)
                    ->whereNotIn('session_id', $keep)
                    ->delete();

                // If the current session was just evicted (edge case), sign out
                $stillValid = DB::table('user_sessions')
                    ->where('user_id', $user->id)
                    ->where('session_id', $sessionId)
                    ->exists();

                if (! $stillValid) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('contensio.login')
                        ->with('status', 'You were signed out because your account is active on too many devices.');
                }
            }
        } catch (\Throwable) {
            // Table may not exist yet (pre-migration) — skip silently
        }

        return $next($request);
    }
}
