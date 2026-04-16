<?php

/**
 * Contensio - The open content platform for Laravel.
 * A flexible content foundation for blogs, shops, communities,
 * and any content-driven app.
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforce a specific permission on a route.
 *
 * Usage:
 *   Route::get(...)->middleware('contensio.permission:users.view')
 *
 * If the user lacks the permission, a 403 is returned with a helpful message.
 */
class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (! $user || ! method_exists($user, 'hasPermission')) {
            abort(403, 'Access denied.');
        }

        if (! $user->hasPermission($permission)) {
            abort(403, "You do not have permission to perform this action ({$permission}).");
        }

        return $next($request);
    }
}
