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

namespace Contensio\Cms\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /** Profile page — name/email, password change, two-factor management. */
    public function index()
    {
        $user = auth()->user();

        // Compute the three 2FA UI states directly from the user columns so
        // the view doesn't have to know about Fortify internals.
        $twoFactor = [
            'enabled'   => ! is_null($user->two_factor_secret) && ! is_null($user->two_factor_confirmed_at),
            'pending'   => ! is_null($user->two_factor_secret) && is_null($user->two_factor_confirmed_at),
            'disabled'  => is_null($user->two_factor_secret),
        ];

        // If 2FA is pending confirmation, fetch the QR code SVG + recovery codes
        // directly from the User model (the TwoFactorAuthenticatable trait
        // provides these accessors). This avoids round-tripping through the
        // Fortify REST endpoints just to render the page.
        $qrCode        = null;
        $recoveryCodes = [];
        if ($twoFactor['pending'] || $twoFactor['enabled']) {
            try {
                $qrCode        = $user->twoFactorQrCodeSvg();
                $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?: [];
            } catch (\Throwable) {
                // Columns may not be populated yet — skip silently
            }
        }

        return view('cms::admin.profile.index', compact('user', 'twoFactor', 'qrCode', 'recoveryCodes'));
    }

    /** Update the user's own name / email. */
    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($data);

        return redirect()
            ->route('cms.admin.profile')
            ->with('success', 'Profile updated.');
    }

    /** Change the current user's own password (requires current password). */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($data['password'])]);

        return redirect()
            ->route('cms.admin.profile')
            ->with('success', 'Password updated.');
    }
}
