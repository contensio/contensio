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

use Contensio\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends Controller
{
    /** Resolve all user-settings flags in one query and return as an array. */
    private function userSettings(): array
    {
        return Setting::where('module', 'users')->pluck('value', 'setting_key')->toArray();
    }

    /** Profile page — name/email, password change, two-factor management. */
    public function index()
    {
        $user     = auth()->user();
        $settings = $this->userSettings();
        $isAdmin  = $user->isSuperAdmin();

        $twoFactor = [
            'enabled'  => ! is_null($user->two_factor_secret) && ! is_null($user->two_factor_confirmed_at),
            'pending'  => ! is_null($user->two_factor_secret) && is_null($user->two_factor_confirmed_at),
            'disabled' => is_null($user->two_factor_secret),
        ];

        $qrCode = null;
        $recoveryCodes = [];
        if ($twoFactor['pending'] || $twoFactor['enabled']) {
            try {
                $qrCode        = $user->twoFactorQrCodeSvg();
                $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?: [];
            } catch (\Throwable) {}
        }

        [$canChangeUsername, $usernameCooldownEnd] = $this->resolveUsernameCooldown($user, $settings, $isAdmin);

        $canChangeEmail   = $isAdmin || ($settings['allow_email_change']   ?? '1') === '1';
        $canEditBio       = $isAdmin || ($settings['allow_bio']            ?? '1') === '1';
        $canUploadAvatar  = $isAdmin || ($settings['allow_avatar']         ?? '1') === '1';
        $canDeleteAccount = ! $isAdmin && ($settings['allow_account_deletion'] ?? '') === '1';

        return view('contensio::admin.profile.index', compact(
            'user', 'twoFactor', 'qrCode', 'recoveryCodes',
            'canChangeUsername', 'usernameCooldownEnd',
            'canChangeEmail', 'canEditBio', 'canUploadAvatar', 'canDeleteAccount'
        ));
    }

    /** Update the user's own name / email / username / bio. */
    public function update(Request $request)
    {
        $user     = auth()->user();
        $settings = $this->userSettings();
        $isAdmin  = $user->isSuperAdmin();

        [$canChangeUsername, $usernameCooldownEnd] = $this->resolveUsernameCooldown($user, $settings, $isAdmin);
        $canChangeEmail = $isAdmin || ($settings['allow_email_change'] ?? '1') === '1';
        $canEditBio     = $isAdmin || ($settings['allow_bio']          ?? '1') === '1';

        $rules = ['name' => ['required', 'string', 'max:255']];

        if ($canChangeEmail) {
            $rules['email'] = ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];
        }
        if ($canEditBio) {
            $rules['bio'] = ['nullable', 'string', 'max:1000'];
        }
        if ($canChangeUsername) {
            $rules['username'] = ['required', 'string', 'min:3', 'max:40', 'regex:/^[a-z0-9_]+$/', Rule::unique('users', 'username')->ignore($user->id)];
        }

        $data = $request->validate($rules);

        $user->name = $data['name'];

        if ($canChangeEmail && isset($data['email'])) {
            $user->email = $data['email'];
        }
        if ($canEditBio) {
            $user->bio = $data['bio'] ?? null;
        }
        if ($canChangeUsername && isset($data['username']) && $data['username'] !== $user->username) {
            $user->username             = $data['username'];
            $user->username_changed_at  = now();
        }

        $user->save();

        return redirect()->route('contensio.account.profile')->with('success', 'Profile updated.');
    }

    /** Change the current user's own password (requires current password). */
    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('contensio.account.profile')->with('success', 'Password updated.');
    }

    /**
     * Accept a cropped avatar JPEG (multipart), store under storage/app/public/avatars/,
     * delete the old one, update the user record, return the new URL.
     */
    public function updateAvatar(Request $request)
    {
        $user         = auth()->user();
        $settings     = $this->userSettings();
        $canUpload    = $user->isSuperAdmin() || ($settings['allow_avatar'] ?? '1') === '1';

        if (! $canUpload) {
            return back()->with('error', 'Avatar uploads are disabled by the administrator.');
        }

        $request->validate([
            'avatar' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            Storage::disk('public')->delete($this->thumbPath($user->avatar_path));
        }

        $suffix   = Str::random(12);
        $filename = "avatars/{$suffix}.jpg";
        $thumb    = $this->thumbPath($filename);
        $src      = $request->file('avatar')->getRealPath();

        Storage::disk('public')->put(
            $filename,
            (string) Image::decode($src)->cover(512, 512)->encode(new JpegEncoder(quality: 85))
        );
        Storage::disk('public')->put(
            $thumb,
            (string) Image::decode($src)->cover(64, 64)->encode(new JpegEncoder(quality: 85))
        );

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

    /** Permanently delete the authenticated user's own account. */
    public function destroy(Request $request)
    {
        $user     = auth()->user();
        $settings = $this->userSettings();

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Administrator accounts cannot be self-deleted.');
        }

        if (($settings['allow_account_deletion'] ?? '') !== '1') {
            return back()->with('error', 'Account deletion is not available.');
        }

        $request->validate(['current_password' => ['required', 'current_password']]);

        $sessionId = $request->session()->getId();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        try {
            DB::table('user_sessions')->where('session_id', $sessionId)->delete();
        } catch (\Throwable) {}

        // Delete avatar files
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            Storage::disk('public')->delete($this->thumbPath($user->avatar_path));
        }

        $user->delete();

        return redirect()->route('contensio.home')
            ->with('status', 'Your account has been permanently deleted.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Resolve whether the user can currently change their username, accounting
     * for the cooldown. Returns [$canChange, $cooldownEndsAt|null].
     */
    private function resolveUsernameCooldown($user, array $settings, bool $isAdmin): array
    {
        $globallyAllowed = $isAdmin || ($settings['users_can_change_username'] ?? '') === '1';

        if (! $globallyAllowed) {
            return [false, null];
        }

        if ($isAdmin) {
            return [true, null]; // admins bypass cooldown
        }

        $cooldownDays = intval($settings['username_cooldown_days'] ?? 0);

        if ($cooldownDays > 0 && $user->username_changed_at) {
            $cooldownEnd = $user->username_changed_at->addDays($cooldownDays);
            if ($cooldownEnd->isFuture()) {
                return [false, $cooldownEnd];
            }
        }

        return [true, null];
    }

    private function thumbPath(string $avatarPath): string
    {
        return dirname($avatarPath) . '/thumb_' . basename($avatarPath);
    }
}
