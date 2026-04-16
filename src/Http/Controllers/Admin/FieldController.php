<?php

/**
 * Contensio - The open content platform for Laravel.
 * Admin — Fields inside a Field Group (create / edit / reorder / delete).
 * https://contensio.com
 *
 * Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * Contensio is operated by Host Server SRL.
 *
 * LICENSE:
 * Permissions of this strongest copyleft license are conditioned on making
 * available complete source code of licensed works and modifications, which
 * include larger works using a licensed work, under the same license.
 * Copyright and license notices must be preserved. Contributors provide an
 * express grant of patent rights. When a modified version is used to provide
 * a service over a network, the complete source code of the modified version
 * must be made available.
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

namespace Contensio\Http\Controllers\Admin;

use Contensio\Models\Field;
use Contensio\Models\FieldGroup;
use Contensio\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class FieldController extends Controller
{
    public function store(Request $request, int $groupId)
    {
        $group = FieldGroup::findOrFail($groupId);
        [$fieldData, $translation] = $this->validated($request, $group->id);

        $fieldData['field_group_id'] = $group->id;
        $fieldData['position']       = ($group->fields()->max('position') ?? -1) + 1;

        $field = Field::create($fieldData);
        $this->syncTranslation($field, $translation);

        return back()->with('success', 'Field added.');
    }

    public function update(Request $request, int $id)
    {
        $field = Field::findOrFail($id);
        [$fieldData, $translation] = $this->validated($request, $field->field_group_id, $field->id);

        $field->update($fieldData);
        $this->syncTranslation($field, $translation);

        return back()->with('success', 'Field updated.');
    }

    protected function syncTranslation(Field $field, array $translation): void
    {
        // Save to the default language — plugins/admin can add other locales later
        $langId = Language::where('is_default', true)->value('id')
            ?? Language::orderBy('id')->value('id');
        if (! $langId) return;

        $field->translations()->updateOrCreate(
            ['language_id' => $langId],
            $translation
        );
    }

    public function destroy(int $id)
    {
        $field = Field::findOrFail($id);
        $field->delete();

        return back()->with('success', 'Field deleted.');
    }

    public function reorder(Request $request, int $groupId)
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach ($request->input('order') as $index => $fieldId) {
            Field::where('id', $fieldId)
                ->where('field_group_id', $groupId)
                ->update(['position' => $index]);
        }

        return response()->json(['ok' => true]);
    }

    protected function validated(Request $request, int $groupId, ?int $ignoreId = null): array
    {
        $uniqueRule = 'unique:fields,key,' . ($ignoreId ?? 'NULL') . ',id,field_group_id,' . $groupId;

        $data = $request->validate([
            'key'             => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', $uniqueRule],
            'label'           => 'required|string|max:200',
            'type'            => 'required|in:' . implode(',', Field::TYPES),
            'section'         => 'nullable|string|max:100',
            'is_translatable' => 'nullable',
            'is_required'     => 'nullable',
            'help_text'       => 'nullable|string|max:500',
            'placeholder'     => 'nullable|string|max:200',
            // Type-specific config — free-form JSON
            'config'          => 'nullable|array',
        ], [
            'key.regex' => 'Key must be lowercase letters, numbers, and underscores only.',
        ]);

        $fieldData = [
            'key'             => Str::lower(trim($data['key'])),
            'type'            => $data['type'],
            'section'         => $data['section'] ?? null,
            'is_translatable' => $request->boolean('is_translatable'),
            'is_required'     => $request->boolean('is_required'),
            'config'          => $this->normalizeConfig($data['type'], $data['config'] ?? []),
        ];

        $translation = [
            'label'       => $data['label'],
            'placeholder' => $data['placeholder'] ?? null,
            'help_text'   => $data['help_text'] ?? null,
        ];

        return [$fieldData, $translation];
    }

    /**
     * Keep only the keys relevant to each field type so the config JSON
     * stays clean and doesn't carry stale values when the type changes.
     */
    protected function normalizeConfig(string $type, array $config): array
    {
        $schema = [
            'text'         => ['max_length'],
            'textarea'     => ['rows', 'max_length'],
            'rich-text'    => [],
            'number'       => ['min', 'max', 'step', 'suffix'],
            'boolean'      => [],
            'date'         => ['with_time'],
            'select'       => ['options'],
            'multi-select' => ['options'],
            'media'        => ['multiple', 'accept'],
            'url'          => [],
        ];

        $allowed = $schema[$type] ?? [];
        $out     = [];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $config) && $config[$key] !== '' && $config[$key] !== null) {
                $out[$key] = $config[$key];
            }
        }

        // Normalize select/multi-select options: accept "value:label\nvalue2:label2" string form too
        if (in_array($type, ['select', 'multi-select'], true) && isset($out['options']) && is_string($out['options'])) {
            $out['options'] = $this->parseOptions($out['options']);
        }

        return $out;
    }

    protected function parseOptions(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($raw));
        $opts  = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $parts = array_map('trim', explode(':', $line, 2));
            $opts[] = [
                'value' => $parts[0],
                'label' => $parts[1] ?? $parts[0],
            ];
        }
        return $opts;
    }
}
