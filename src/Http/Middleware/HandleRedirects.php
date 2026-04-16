<?php

/**
 * Contensio - The open content platform for Laravel.
 * Middleware that serves admin-configured URL redirects.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 */

namespace Contensio\Cms\Http\Middleware;

use Closure;
use Contensio\Cms\Models\Redirect as RedirectModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HandleRedirects
{
    /**
     * Check the incoming URL against the `redirects` table and, if a match
     * is found, respond with the configured 301/302 to the target URL.
     *
     * Only GET/HEAD requests are checked. The admin panel and auth routes
     * are skipped — redirects only affect the public site.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $path = '/' . ltrim($request->path(), '/');

        // Skip admin + auth paths — they're never redirect candidates
        $adminPrefix = '/' . trim((string) config('cms.route_prefix', 'admin'), '/');
        if ($path === $adminPrefix || str_starts_with($path, $adminPrefix . '/')) {
            return $next($request);
        }
        if (in_array($path, ['/login', '/logout', '/register'], true)) {
            return $next($request);
        }

        try {
            $hit = RedirectModel::where('source_url', $path)->first();
        } catch (\Throwable) {
            return $next($request); // table missing during install
        }

        if (! $hit) {
            return $next($request);
        }

        // Increment hit counter — fire-and-forget, don't let DB errors stop the redirect
        try {
            DB::table('redirects')
                ->where('id', $hit->id)
                ->update(['hits' => DB::raw('hits + 1'), 'last_hit_at' => now()]);
        } catch (\Throwable) {
            // non-fatal
        }

        $target = $hit->target_url;
        if (str_starts_with($target, '/')) {
            $target = url($target);
        }

        return redirect()->away($target, (int) ($hit->status_code ?: 301));
    }
}
