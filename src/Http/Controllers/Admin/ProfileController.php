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

namespace Contensio\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;

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

        return view('contensio::admin.profile.index', compact('user', 'twoFactor', 'qrCode', 'recoveryCodes'));
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
            ->route('contensio.account.profile')
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
            ->route('contensio.account.profile')
            ->with('success', 'Password updated.');
    }

    /**
     * Accept a cropped avatar JPEG (multipart), store it under storage/app/public/avatars/,
     * delete the old one, update the user record, return the new URL.
     *
     * The file is produced client-side by Cropper.js (canvas → blob → FormData),
     * so we validate size + MIME on the server too.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user = auth()->user();

        // Remove previous files if present
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            Storage::disk('public')->delete($this->thumbPath($user->avatar_path));
        }

        $suffix   = Str::random(12);
        $filename = "avatars/{$suffix}.jpg";
        $thumb    = $this->thumbPath($filename);

        $sourcePath = $request->file('avatar')->getRealPath();

        // Full avatar (512×512)
        Storage::disk('public')->put(
            $filename,
            (string) Image::decode($sourcePath)->cover(512, 512)->encode(new JpegEncoder(quality: 85))
        );

        // Thumbnail (64×64)
        Storage::disk('public')->put(
            $thumb,
            (string) Image::decode($sourcePath)->cover(64, 64)->encode(new JpegEncoder(quality: 85))
        );

        // Direct assignment bypasses $fillable so the package does not need to
        // control the host app's User model.
        $user->avatar_path = $filename;
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'url'       => Storage::disk('public')->url($filename),
                'thumb_url' => Storage::disk('public')->url($thumb),
            ]);
        }

        return back()->with('success', 'Avatar updated.');
    }

    public function removeAvatar()
    {
        $user = auth()->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            Storage::disk('public')->delete($this->thumbPath($user->avatar_path));

            $user->avatar_path = null;
            $user->save();
        }

        return back()->with('success', 'Avatar removed.');
    }

    /**
     * Derive the 64×64 thumbnail path from the full avatar path.
     * e.g. avatars/5-abc123.jpg → avatars/thumb_5-abc123.jpg
     */
    private function thumbPath(string $avatarPath): string
    {
        return dirname($avatarPath) . '/thumb_' . basename($avatarPath);
    }
}
