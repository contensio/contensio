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

namespace Contensio\Cms\Support;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Notifications\Messages\MailMessage;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

/**
 * Wires Laravel Fortify into Contensio.
 *
 * What we use Fortify for:
 *   - Password reset (request link, reset form, email)
 *   - Email verification (optional; view + routes)
 *   - Password confirmation (for sensitive admin actions, future use)
 *
 * What we do NOT use Fortify for:
 *   - Login — Contensio has its own LoginController with is_active checks
 *     and role-aware redirect, so Fortify's login feature is disabled.
 *   - Registration — Contensio is admin-managed; users are created by
 *     administrators, not self-service. Fortify's register feature is off.
 *
 * Runs from CmsServiceProvider::boot() so it kicks in only when the CMS
 * itself is active (not during package install / before migrations).
 */
class FortifyIntegration
{
    /**
     * Configure Fortify — sets feature flags, view callbacks, and the
     * route paths that point back at our own login / home.
     */
    public static function configure(Application $app): void
    {
        // ── Feature set ──────────────────────────────────────────────────
        // Fortify's login / register features stay off — we keep our own.
        // 2FA is enabled with confirm=true so users must verify a valid TOTP
        // before 2FA becomes active on their account.
        $features = [
            Features::resetPasswords(),
            Features::emailVerification(),
            Features::twoFactorAuthentication([
                'confirm'        => true,
                'confirmPassword' => true,
            ]),
        ];
        $app['config']->set('fortify.features', $features);

        // ── Route paths ──────────────────────────────────────────────────
        // Keep Fortify's default paths (/forgot-password, /reset-password, etc.)
        // but point the "home" after verification to Contensio's dashboard.
        $app['config']->set('fortify.home', '/' . ltrim(config('cms.route_prefix', 'admin'), '/'));

        // Contensio renders its own login — Fortify's /login route is disabled
        // by omitting Features::login(). BUT Fortify's route file unconditionally
        // binds GET+POST /login regardless of feature flags — we re-map them to
        // dead paths so they can't override our cms.login route.
        $app['config']->set('fortify.paths.login',  '__fortify_login_disabled__');
        $app['config']->set('fortify.paths.logout', '__fortify_logout_disabled__');

        // ── View callbacks ───────────────────────────────────────────────
        // Fortify is headless — we register callbacks that return our Blade views.
        Fortify::requestPasswordResetLinkView(fn () => view('cms::auth.forgot-password'));

        Fortify::resetPasswordView(fn ($request) => view('cms::auth.reset-password', [
            'request' => $request,
            'token'   => $request->route('token'),
            'email'   => $request->query('email'),
        ]));

        Fortify::verifyEmailView(fn () => view('cms::auth.verify-email'));

        Fortify::confirmPasswordView(fn () => view('cms::auth.confirm-password'));

        Fortify::twoFactorChallengeView(fn () => view('cms::auth.two-factor-challenge'));

        // ── Customize password-reset email copy ──────────────────────────
        // Fortify uses Laravel's default ResetPassword notification. We
        // override the rendered body so it's cleanly branded and clear.
        ResetPasswordNotification::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $siteName = config('cms.name', 'Contensio');

            return (new MailMessage)
                ->subject('Reset your ' . $siteName . ' password')
                ->greeting('Hi ' . ($notifiable->name ?? '') . ',')
                ->line('You recently requested to reset the password for your ' . $siteName . ' account.')
                ->action('Reset password', $url)
                ->line('This link will expire in ' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60) . ' minutes.')
                ->line('If you did not request a password reset, you can safely ignore this email — no changes will be made.')
                ->salutation('— The ' . $siteName . ' team');
        });
    }
}
