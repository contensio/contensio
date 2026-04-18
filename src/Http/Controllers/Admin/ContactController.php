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

use Contensio\Models\ContactField;
use Contensio\Models\ContactFieldTranslation;
use Contensio\Models\ContactMessage;
use Contensio\Models\Language;
use Contensio\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    // ─── Main tabbed page ────────────────────────────────────────────────────

    public function index()
    {
        // Seed default fields on first visit
        ContactField::seedDefaults();

        $fields    = ContactField::with('translations')->orderBy('sort_order')->get();
        $languages = Language::active()->get();
        $settings  = $this->loadSettings();
        $unread    = ContactMessage::where('status', ContactMessage::STATUS_NEW)->count();

        return view('contensio::admin.contact.index', compact(
            'fields', 'languages', 'settings', 'unread'
        ));
    }

    // ─── Builder ─────────────────────────────────────────────────────────────

    public function saveBuilder(Request $request)
    {
        $sections = json_decode($request->input('sections', '[]'), true) ?? [];

        // Ensure each section has an id
        $sections = array_map(function ($section) {
            $section['id'] ??= Str::uuid()->toString();
            return $section;
        }, $sections);

        Setting::updateOrCreate(
            ['module' => 'contact', 'setting_key' => 'sections'],
            ['value' => json_encode($sections), 'updated_at' => now()]
        );

        return back()->with('success', __('contensio::admin.contact.saved'));
    }

    // ─── Appearance ──────────────────────────────────────────────────────────

    public function saveAppearance(Request $request)
    {
        $data = $request->validate([
            'field_size' => 'required|in:small,normal,large',
            'layout'     => 'required|in:classic,wide,split,card',
        ]);

        Setting::updateOrCreate(
            ['module' => 'contact', 'setting_key' => 'appearance'],
            ['value' => json_encode($data), 'updated_at' => now()]
        );

        return back()->with('success', __('contensio::admin.contact.saved'));
    }

    // ─── Settings ────────────────────────────────────────────────────────────

    public function saveSettings(Request $request)
    {
        $languages = Language::active()->get();

        // Slug per language
        $slugs = [];
        foreach ($languages as $lang) {
            $raw = $request->input("slug_{$lang->code}");
            $slugs[$lang->code] = $raw ? Str::slug($raw) : 'contact';
        }
        if (empty($slugs)) {
            $slugs['en'] = $request->input('slug', 'contact') ?: 'contact';
        }

        Setting::updateOrCreate(
            ['module' => 'contact', 'setting_key' => 'slugs'],
            ['value' => json_encode($slugs), 'updated_at' => now()]
        );

        // Success message per language
        $successMessages = [];
        foreach ($languages as $lang) {
            $successMessages[$lang->code] = $request->input("success_message_{$lang->code}", '');
        }
        Setting::updateOrCreate(
            ['module' => 'contact', 'setting_key' => 'success_message'],
            ['value' => json_encode($successMessages), 'updated_at' => now()]
        );

        // Redirect
        Setting::updateOrCreate(
            ['module' => 'contact', 'setting_key' => 'redirect'],
            ['value' => json_encode([
                'type' => $request->input('redirect_type', 'same_page'),
                'url'  => $request->input('redirect_url', ''),
            ]), 'updated_at' => now()]
        );

        // Antispam
        Setting::updateOrCreate(
            ['module' => 'contact', 'setting_key' => 'antispam'],
            ['value' => json_encode([
                'honeypot'      => (bool) $request->input('honeypot', true),
                'time_check'    => (bool) $request->input('time_check', true),
                'min_seconds'   => (int) $request->input('min_seconds', 3),
                'rate_limit'    => [
                    'enabled'     => (bool) $request->input('rate_limit_enabled'),
                    'max'         => (int) $request->input('rate_limit_max', 5),
                    'per_minutes' => (int) $request->input('rate_limit_minutes', 60),
                ],
                'math_question' => [
                    'enabled' => (bool) $request->input('math_question_enabled'),
                ],
                'recaptcha' => [
                    'enabled'    => (bool) $request->input('recaptcha_enabled'),
                    'site_key'   => $request->input('recaptcha_site_key', ''),
                    'secret_key' => $request->input('recaptcha_secret_key', ''),
                ],
                'turnstile' => [
                    'enabled'    => (bool) $request->input('turnstile_enabled'),
                    'site_key'   => $request->input('turnstile_site_key', ''),
                    'secret_key' => $request->input('turnstile_secret_key', ''),
                ],
            ]), 'updated_at' => now()]
        );

        // Notifications
        Setting::updateOrCreate(
            ['module' => 'contact', 'setting_key' => 'notifications'],
            ['value' => json_encode([
                'admin_email'  => $request->input('admin_email', ''),
                'auto_reply'   => [
                    'enabled' => (bool) $request->input('auto_reply_enabled'),
                    'subject' => $this->collectPerLang($request, 'auto_reply_subject', $languages),
                    'body'    => $this->collectPerLang($request, 'auto_reply_body', $languages),
                ],
                'webhook' => [
                    'enabled' => (bool) $request->input('webhook_enabled'),
                    'url'     => $request->input('webhook_url', ''),
                ],
            ]), 'updated_at' => now()]
        );

        // File uploads
        Setting::updateOrCreate(
            ['module' => 'contact', 'setting_key' => 'file_uploads'],
            ['value' => json_encode([
                'enabled'       => (bool) $request->input('file_uploads_enabled'),
                'max_files'     => (int) $request->input('max_files', 3),
                'max_size_mb'   => (int) $request->input('max_size_mb', 5),
                'allowed_types' => array_filter(explode(',', $request->input('allowed_types', 'jpg,png,pdf'))),
            ]), 'updated_at' => now()]
        );

        // GDPR
        Setting::updateOrCreate(
            ['module' => 'contact', 'setting_key' => 'gdpr'],
            ['value' => json_encode([
                'enabled'  => (bool) $request->input('gdpr_enabled'),
                'required' => (bool) $request->input('gdpr_required', true),
                'text'     => $this->collectPerLang($request, 'gdpr_text', $languages),
            ]), 'updated_at' => now()]
        );

        return back()->with('success', __('contensio::admin.contact.saved'));
    }

    // ─── Fields ──────────────────────────────────────────────────────────────

    public function storeField(Request $request)
    {
        $request->validate([
            'type' => 'required|in:text,textarea,select,multiselect,phone,date,url,email,rating,checkbox,file',
            'key'  => 'required|regex:/^[a-z][a-z0-9_]*$/|unique:contact_fields,key',
        ]);

        $maxOrder = ContactField::max('sort_order') ?? 0;

        $field = ContactField::create([
            'type'       => $request->input('type'),
            'key'        => $request->input('key'),
            'required'   => (bool) $request->input('required'),
            'width'      => in_array($request->input('width'), ['full', 'half', '1/3', '1/4']) ? $request->input('width') : 'full',
            'sort_order' => $maxOrder + 10,
            'options'    => $this->parseFieldOptions($request),
            'conditional' => $request->input('conditional_field')
                ? ['field' => $request->input('conditional_field'), 'operator' => $request->input('conditional_operator', 'equals'), 'value' => $request->input('conditional_value', '')]
                : null,
        ]);

        $languages = Language::active()->get();
        foreach ($languages as $lang) {
            ContactFieldTranslation::create([
                'contact_field_id' => $field->id,
                'language_id'      => $lang->id,
                'label'            => $request->input("label_{$lang->code}") ?: ucfirst(str_replace('_', ' ', $request->input('key'))),
                'placeholder'      => $request->input("placeholder_{$lang->code}"),
                'help_text'        => $request->input("help_text_{$lang->code}"),
            ]);
        }

        return redirect()->route('contensio.account.contact.index', ['tab' => 'fields'])
            ->with('success', 'Field added.');
    }

    public function updateField(Request $request, int $id)
    {
        $field = ContactField::findOrFail($id);

        $field->update([
            'required'    => (bool) $request->input('required'),
            'width'       => in_array($request->input('width'), ['full', 'half', '1/3', '1/4']) ? $request->input('width') : 'full',
            'options'     => $this->parseFieldOptions($request),
            'conditional' => $request->input('conditional_field')
                ? ['field' => $request->input('conditional_field'), 'operator' => $request->input('conditional_operator', 'equals'), 'value' => $request->input('conditional_value', '')]
                : null,
        ]);

        $languages = Language::active()->get();
        foreach ($languages as $lang) {
            ContactFieldTranslation::updateOrCreate(
                ['contact_field_id' => $field->id, 'language_id' => $lang->id],
                [
                    'label'       => $request->input("label_{$lang->code}") ?: $field->key,
                    'placeholder' => $request->input("placeholder_{$lang->code}"),
                    'help_text'   => $request->input("help_text_{$lang->code}"),
                ]
            );
        }

        return redirect()->route('contensio.account.contact.index', ['tab' => 'fields'])
            ->with('success', 'Field updated.');
    }

    public function destroyField(int $id)
    {
        $field = ContactField::findOrFail($id);

        if ($field->is_default) {
            return back()->with('error', 'Default fields cannot be deleted.');
        }

        $field->delete();

        return back()->with('success', 'Field deleted.');
    }

    public function reorderFields(Request $request)
    {
        $ids = array_filter(array_map('intval', $request->input('ids', [])));

        foreach ($ids as $position => $id) {
            ContactField::where('id', $id)->update(['sort_order' => ($position + 1) * 10]);
        }

        return response()->json(['ok' => true]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    protected function loadSettings(): array
    {
        $raw = Setting::where('module', 'contact')
            ->pluck('value', 'setting_key')
            ->toArray();

        return [
            'sections'       => json_decode($raw['sections'] ?? '[]', true) ?? [],
            'slugs'          => json_decode($raw['slugs'] ?? '{}', true) ?? ['en' => 'contact'],
            'appearance'     => json_decode($raw['appearance'] ?? '{}', true) ?? ['field_size' => 'normal', 'layout' => 'classic'],
            'success_message'=> json_decode($raw['success_message'] ?? '{}', true) ?? [],
            'redirect'       => json_decode($raw['redirect'] ?? '{}', true) ?? ['type' => 'same_page', 'url' => ''],
            'antispam'       => json_decode($raw['antispam'] ?? '{}', true) ?? $this->defaultAntispam(),
            'notifications'  => json_decode($raw['notifications'] ?? '{}', true) ?? $this->defaultNotifications(),
            'file_uploads'   => json_decode($raw['file_uploads'] ?? '{}', true) ?? $this->defaultFileUploads(),
            'gdpr'           => json_decode($raw['gdpr'] ?? '{}', true) ?? ['enabled' => false, 'required' => true, 'text' => []],
        ];
    }

    protected function defaultAntispam(): array
    {
        return [
            'honeypot'      => true,
            'time_check'    => true,
            'min_seconds'   => 3,
            'rate_limit'    => ['enabled' => true, 'max' => 5, 'per_minutes' => 60],
            'math_question' => ['enabled' => false],
            'recaptcha'     => ['enabled' => false, 'site_key' => '', 'secret_key' => ''],
            'turnstile'     => ['enabled' => false, 'site_key' => '', 'secret_key' => ''],
        ];
    }

    protected function defaultNotifications(): array
    {
        return [
            'admin_email' => '',
            'auto_reply'  => ['enabled' => false, 'subject' => [], 'body' => []],
            'webhook'     => ['enabled' => false, 'url' => ''],
        ];
    }

    protected function defaultFileUploads(): array
    {
        return ['enabled' => false, 'max_files' => 3, 'max_size_mb' => 5, 'allowed_types' => ['jpg', 'png', 'pdf']];
    }

    protected function collectPerLang(Request $request, string $prefix, $languages): array
    {
        $values = [];
        foreach ($languages as $lang) {
            $values[$lang->code] = $request->input("{$prefix}_{$lang->code}", '');
        }
        return $values;
    }

    protected function parseFieldOptions(Request $request): ?array
    {
        $type = $request->input('type');

        if (in_array($type, ContactField::CHOICE_TYPES)) {
            $raw = $request->input('options_text', '');
            $choices = array_filter(array_map('trim', explode("\n", $raw)));
            return ['choices' => array_values($choices)];
        }

        if ($type === 'text' || $type === 'textarea') {
            return array_filter([
                'min_length' => $request->integer('min_length') ?: null,
                'max_length' => $request->integer('max_length') ?: null,
            ]);
        }

        return null;
    }
}
