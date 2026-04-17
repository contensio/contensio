<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Auth;

use App\Models\User;
use Contensio\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('contensio.home');
        }

        $disabled = Setting::where('module', 'users')
            ->where('setting_key', 'registration_disabled')
            ->value('value') === '1';

        return view('contensio::auth.register', compact('disabled'));
    }

    public function register(Request $request)
    {
        $userSettings = Setting::where('module', 'users')
            ->pluck('value', 'setting_key');

        // Block submission even if someone bypasses the UI
        if (($userSettings['registration_disabled'] ?? '') === '1') {
            return back()->with('error', 'Registration is currently disabled.');
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:5', 'max:25', 'regex:/^[a-z0-9_]+$/', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $requireApproval         = ($userSettings['require_approval']             ?? '') === '1';
        $verificationDisabled    = ($userSettings['email_verification_disabled']  ?? '') === '1';
        $defaultRoleId           = intval($userSettings['default_registration_role_id'] ?? 0);

        $user = User::create([
            'name'              => $data['name'],
            'username'          => $data['username'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'code'              => Str::random(16),
            'is_active'         => ! $requireApproval,
            'email_verified_at' => ($verificationDisabled && ! $requireApproval) ? now() : null,
        ]);

        // Assign default role if configured
        if ($defaultRoleId > 0) {
            $user->roles()->sync([$defaultRoleId]);
        }

        // Send email verification if needed
        if (! $verificationDisabled && ! $requireApproval && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
        }

        if ($requireApproval) {
            return redirect()->route('contensio.login')
                ->with('status', 'Your account has been created and is awaiting admin approval.');
        }

        Auth::login($user);

        return redirect()->route('contensio.home');
    }
}
