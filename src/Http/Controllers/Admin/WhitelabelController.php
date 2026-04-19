<?php

/**
 * Contensio - The open content platform for Laravel.
 * White-label admin controller.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Http\Controllers\Admin;

use Contensio\Models\Setting;
use Contensio\Services\LicenseService;
use Contensio\Services\WhitelabelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WhitelabelController extends Controller
{
    public function index(): View
    {
        $settings = Setting::where('module', 'whitelabel')
            ->pluck('value', 'setting_key')
            ->toArray();

        $appDomain = parse_url(config('app.url', ''), PHP_URL_HOST) ?? request()->getHost();

        // Parse the stored key live so the view gets fresh payload data.
        $licensePayload = null;
        if (! empty($settings['license_key'])) {
            $result = LicenseService::parse($settings['license_key']);
            if ($result['valid']) {
                $licensePayload = $result['payload'];
            }
        }

        return view('contensio::admin.whitelabel.index', compact('settings', 'appDomain', 'licensePayload'));
    }

    public function saveLicense(Request $request): RedirectResponse
    {
        $request->validate([
            'license_key' => ['required', 'string', 'max:2000'],
        ]);

        $key    = trim($request->input('license_key'));
        $result = LicenseService::parse($key);

        if (! $result['valid']) {
            return back()->withErrors(['license_key' => $result['error']])->withInput();
        }

        // Store ONLY the raw key. Status is always derived from signature verification —
        // never from a cached "status" value that could be forged via a direct DB write.
        $this->set('license_key', $key);

        WhitelabelService::flush();

        return back()->with('success', 'License key activated successfully.');
    }

    public function removeLicense(): RedirectResponse
    {
        Setting::where('module', 'whitelabel')->where('setting_key', 'license_key')->delete();

        WhitelabelService::flush();

        return back()->with('success', 'License key removed. White-label features are now disabled.');
    }

    public function saveBranding(Request $request): RedirectResponse
    {
        $request->validate([
            'admin_logo'        => ['nullable', 'file', 'image', 'max:2048'],
            'admin_logo_dark'   => ['nullable', 'file', 'image', 'max:2048'],
            'admin_favicon'     => ['nullable', 'file', 'mimes:png,ico,svg,gif', 'max:512'],
            'login_bg_image'    => ['nullable', 'file', 'image', 'max:4096'],
            'hide_powered_by'   => ['nullable', 'boolean'],
            'hide_admin_footer' => ['nullable', 'boolean'],
            'admin_name'        => ['nullable', 'string', 'max:100'],
            'email_sender_name' => ['nullable', 'string', 'max:100'],
            'email_footer_text' => ['nullable', 'string', 'max:500'],
            'accent_color'      => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_dark_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sidebar_bg_color'  => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'login_bg_color'    => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'login_tagline'     => ['nullable', 'string', 'max:200'],
        ]);

        // ── File uploads ──────────────────────────────────────────────────────
        if ($request->hasFile('admin_logo')) {
            $url = $this->storeAsset($request->file('admin_logo'), 'admin-logo');
            $this->set('admin_logo_url', $url);
        }

        if ($request->hasFile('admin_logo_dark')) {
            $url = $this->storeAsset($request->file('admin_logo_dark'), 'admin-logo-dark');
            $this->set('admin_logo_dark_url', $url);
        }

        if ($request->hasFile('admin_favicon')) {
            $url = $this->storeAsset($request->file('admin_favicon'), 'admin-favicon');
            $this->set('admin_favicon_url', $url);
        }

        if ($request->hasFile('login_bg_image')) {
            $url = $this->storeAsset($request->file('login_bg_image'), 'login-bg');
            $this->set('login_bg_image_url', $url);
        }

        // ── Booleans (only save when present in this form's submission) ───────
        if ($request->has('hide_powered_by')) {
            $this->set('hide_powered_by', $request->boolean('hide_powered_by') ? '1' : '0');
        }

        if ($request->has('hide_admin_footer')) {
            $this->set('hide_admin_footer', $request->boolean('hide_admin_footer') ? '1' : '0');
        }

        // ── Text & color settings ─────────────────────────────────────────────
        $textFields = [
            'admin_name', 'email_sender_name', 'email_footer_text',
            'accent_color', 'accent_dark_color', 'sidebar_bg_color',
            'login_bg_color', 'login_tagline',
        ];

        foreach ($textFields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field, '');
                $this->set($field, $value);
            }
        }

        WhitelabelService::flush();

        return back()->with('success', 'Saved.');
    }

    public function resetBranding(Request $request): RedirectResponse
    {
        $field = $request->input('field');

        $allowed = ['admin_logo_url', 'admin_logo_dark_url', 'admin_favicon_url', 'login_bg_image_url'];

        if (in_array($field, $allowed, true)) {
            Setting::where('module', 'whitelabel')->where('setting_key', $field)->delete();
        }

        WhitelabelService::flush();

        return back()->with('success', 'Reset to default.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function set(string $key, string $value): void
    {
        Setting::updateOrCreate(
            ['module' => 'whitelabel', 'setting_key' => $key],
            ['value' => $value]
        );
    }

    private function storeAsset(\Illuminate\Http\UploadedFile $file, string $name): string
    {
        $ext      = $file->getClientOriginalExtension();
        $filename = $name . '.' . $ext;

        Storage::disk('public')->put('whitelabel/' . $filename, file_get_contents($file->getRealPath()));

        return asset('storage/whitelabel/' . $filename);
    }
}
