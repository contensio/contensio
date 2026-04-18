<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Frontend;

use Contensio\Models\ContactField;
use Contensio\Models\ContactMessage;
use Contensio\Models\ContactMessageFile;
use Contensio\Models\Language;
use Contensio\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class ContactPageController extends Controller
{
    public function show(Request $request)
    {
        [$settings, $fields, $lang, $site] = $this->pageData();

        return view('theme::contact', compact('settings', 'fields', 'lang', 'site'));
    }

    public function submit(Request $request)
    {
        [$settings, $fields, $lang, $site] = $this->pageData();

        $antispam = $settings['antispam'];

        // ── Honeypot ──────────────────────────────────────────────────────────
        if (($antispam['honeypot'] ?? true) && $request->filled('_hp_website')) {
            return $this->successResponse($settings, $lang);
        }

        // ── Time check ────────────────────────────────────────────────────────
        if ($antispam['time_check'] ?? true) {
            $formTime = (int) $request->input('_form_time', 0);
            $minSecs  = (int) ($antispam['min_seconds'] ?? 3);
            if ($formTime && (time() - $formTime) < $minSecs) {
                return back()->withErrors(['_antispam' => 'Please wait a moment before submitting.'])->withInput();
            }
        }

        // ── Rate limiting ─────────────────────────────────────────────────────
        $rateCfg = $antispam['rate_limit'] ?? [];
        if ($rateCfg['enabled'] ?? false) {
            $key     = 'contact_submit_' . $request->ip();
            $max     = (int) ($rateCfg['max'] ?? 5);
            $minutes = (int) ($rateCfg['per_minutes'] ?? 60);
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return back()->withErrors(['_antispam' => 'Too many submissions. Please try again later.'])->withInput();
            }
            RateLimiter::hit($key, $minutes * 60);
        }

        // ── Math question ─────────────────────────────────────────────────────
        $mathCfg = $antispam['math_question'] ?? [];
        if ($mathCfg['enabled'] ?? false) {
            $a      = (int) $request->session()->get('contact_math_a', 0);
            $b      = (int) $request->session()->get('contact_math_b', 0);
            $answer = (int) $request->input('math_answer', -999);
            if ($a + $b !== $answer) {
                return back()->withErrors(['_antispam' => 'Incorrect answer. Please try again.'])->withInput();
            }
            // Regenerate so the same answer can't be replayed
            $request->session()->forget(['contact_math_a', 'contact_math_b']);
        }

        // ── reCAPTCHA ─────────────────────────────────────────────────────────
        $recaptcha = $antispam['recaptcha'] ?? [];
        if (($recaptcha['enabled'] ?? false) && ! empty($recaptcha['secret_key'])) {
            $response = $request->input('g-recaptcha-response', '');
            if (! $this->verifyRecaptcha($response, $recaptcha['secret_key'])) {
                return back()->withErrors(['_antispam' => 'reCAPTCHA verification failed.'])->withInput();
            }
        }

        // ── Cloudflare Turnstile ──────────────────────────────────────────────
        $turnstile = $antispam['turnstile'] ?? [];
        if (($turnstile['enabled'] ?? false) && ! empty($turnstile['secret_key'])) {
            $token = $request->input('cf-turnstile-response', '');
            if (! $this->verifyTurnstile($token, $turnstile['secret_key'])) {
                return back()->withErrors(['_antispam' => 'Security check failed.'])->withInput();
            }
        }

        // ── Validate required fields ──────────────────────────────────────────
        $rules = [
            'name'    => 'required|string|max:200',
            'email'   => 'required|email|max:200',
            'subject' => 'nullable|string|max:500',
            'message' => 'required|string|max:10000',
        ];

        // GDPR consent
        $gdpr = $settings['gdpr'] ?? [];
        if (($gdpr['enabled'] ?? false) && ($gdpr['required'] ?? true)) {
            $rules['gdpr_consent'] = 'accepted';
        }

        // Extra fields
        $extraFields = $fields->filter(fn ($f) => ! $f->is_default);
        foreach ($extraFields as $field) {
            if ($field->required) {
                $rules["extra_{$field->key}"] = 'required';
            }
        }

        // File uploads
        $fileCfg = $settings['file_uploads'] ?? [];
        if ($fileCfg['enabled'] ?? false) {
            $maxFiles   = (int) ($fileCfg['max_files'] ?? 3);
            $maxSizeMb  = (int) ($fileCfg['max_size_mb'] ?? 5);
            $maxSizeKb  = $maxSizeMb * 1024;
            $allowedExt = implode(',', (array) ($fileCfg['allowed_types'] ?? ['jpg', 'png', 'pdf']));
            $rules['attachments']   = "nullable|array|max:{$maxFiles}";
            $rules['attachments.*'] = "file|max:{$maxSizeKb}|mimes:{$allowedExt}";
        }

        $validated = $request->validate($rules);

        // ── Build extra data ──────────────────────────────────────────────────
        $extraData = [];
        foreach ($extraFields as $field) {
            $val = $request->input("extra_{$field->key}");
            if ($val !== null) {
                $extraData[$field->key] = is_array($val) ? $val : (string) $val;
            }
        }

        // ── Create message ────────────────────────────────────────────────────
        $message = ContactMessage::create([
            'status'     => ContactMessage::STATUS_NEW,
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'subject'    => $validated['subject'] ?? null,
            'message'    => $validated['message'],
            'extra_data' => $extraData ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // ── Handle file uploads ───────────────────────────────────────────────
        if (! empty($validated['attachments'])) {
            foreach ($validated['attachments'] as $file) {
                $path = $file->store('contact-uploads/' . date('Y/m'), 'public');
                ContactMessageFile::create([
                    'contact_message_id' => $message->id,
                    'disk'               => 'public',
                    'file_path'          => $path,
                    'file_name'          => $file->getClientOriginalName(),
                    'mime_type'          => $file->getMimeType(),
                    'size'               => $file->getSize(),
                    'created_at'         => now(),
                ]);
            }
        }

        do_action('contensio/contact/submitted', $message);

        // ── Admin notification email ──────────────────────────────────────────
        $notifications = $settings['notifications'] ?? [];
        $adminEmail    = $notifications['admin_email'] ?? config('mail.from.address', '');
        if ($adminEmail) {
            try {
                Mail::send([], [], function ($mail) use ($message, $adminEmail, $site) {
                    $body  = "<p><strong>Name:</strong> {$message->name}<br>";
                    $body .= "<strong>Email:</strong> {$message->email}<br>";
                    if ($message->subject) $body .= "<strong>Subject:</strong> {$message->subject}<br>";
                    $body .= "</p><p>" . nl2br(e($message->message)) . "</p>";

                    $mail->to($adminEmail)
                         ->subject("[{$site['name']}] New contact message from {$message->name}")
                         ->setBody($body, 'text/html');
                });
            } catch (\Throwable) {}
        }

        // ── Auto-reply to sender ──────────────────────────────────────────────
        $autoReply = $notifications['auto_reply'] ?? [];
        if (($autoReply['enabled'] ?? false)) {
            try {
                $locale  = app()->getLocale();
                $subject = $autoReply['subject'][$locale] ?? $autoReply['subject']['en'] ?? "We received your message";
                $body    = $autoReply['body'][$locale] ?? $autoReply['body']['en'] ?? "Thank you for contacting us. We'll get back to you soon.";

                Mail::send([], [], function ($mail) use ($message, $subject, $body) {
                    $mail->to($message->email, $message->name)
                         ->subject($subject)
                         ->setBody(nl2br(e($body)) . "<br><br><em>Your message: " . nl2br(e($message->message)) . "</em>", 'text/html');
                });
            } catch (\Throwable) {}
        }

        // ── Webhook ───────────────────────────────────────────────────────────
        $webhook = $notifications['webhook'] ?? [];
        if (($webhook['enabled'] ?? false) && ! empty($webhook['url'])) {
            try {
                \Illuminate\Support\Facades\Http::post($webhook['url'], [
                    'id'      => $message->id,
                    'name'    => $message->name,
                    'email'   => $message->email,
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'date'    => $message->created_at->toIso8601String(),
                ]);
            } catch (\Throwable) {}
        }

        return $this->successResponse($settings, $lang);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    protected function pageData(): array
    {
        $raw = Setting::where('module', 'contact')
            ->pluck('value', 'setting_key')
            ->toArray();

        $settings = [
            'sections'       => json_decode($raw['sections'] ?? '[]', true) ?? [],
            'slugs'          => json_decode($raw['slugs'] ?? '{}', true) ?? ['en' => 'contact'],
            'appearance'     => json_decode($raw['appearance'] ?? '{}', true) ?? ['field_size' => 'normal', 'layout' => 'classic'],
            'success_message'=> json_decode($raw['success_message'] ?? '{}', true) ?? [],
            'redirect'       => json_decode($raw['redirect'] ?? '{}', true) ?? ['type' => 'same_page', 'url' => ''],
            'antispam'       => json_decode($raw['antispam'] ?? '{}', true) ?? [],
            'notifications'  => json_decode($raw['notifications'] ?? '{}', true) ?? [],
            'file_uploads'   => json_decode($raw['file_uploads'] ?? '{}', true) ?? ['enabled' => false],
            'gdpr'           => json_decode($raw['gdpr'] ?? '{}', true) ?? ['enabled' => false],
        ];

        $fields = ContactField::with('translations')->orderBy('sort_order')->get();
        $lang   = Language::where('code', app()->getLocale())->first();
        $site   = config('contensio.site', ['name' => config('app.name')]);

        return [$settings, $fields, $lang, $site];
    }

    protected function successResponse(array $settings, mixed $lang): \Illuminate\Http\RedirectResponse
    {
        $redirect = $settings['redirect'] ?? [];
        $locale   = app()->getLocale();

        $successMsg = $settings['success_message'][$locale]
            ?? $settings['success_message']['en']
            ?? 'Thank you! Your message has been sent.';

        if (($redirect['type'] ?? 'same_page') === 'url' && ! empty($redirect['url'])) {
            return redirect($redirect['url'])->with('contact_success', $successMsg);
        }

        return back()->with('contact_success', $successMsg);
    }

    protected function verifyRecaptcha(string $response, string $secret): bool
    {
        try {
            $result = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $response,
            ]);
            return (bool) ($result->json('success') ?? false);
        } catch (\Throwable) {
            return true; // fail open — don't block on service outage
        }
    }

    protected function verifyTurnstile(string $token, string $secret): bool
    {
        try {
            $result = \Illuminate\Support\Facades\Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'   => $secret,
                'response' => $token,
            ]);
            return (bool) ($result->json('success') ?? false);
        } catch (\Throwable) {
            return true;
        }
    }
}
