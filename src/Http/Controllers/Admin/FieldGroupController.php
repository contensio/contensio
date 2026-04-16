<?php

/**
 * Contensio - The open content platform for Laravel.
 * Admin — Custom Field Groups (reusable libraries of fields that attach to
 * content types).
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

namespace Contensio\Cms\Http\Controllers\Admin;

use Contensio\Cms\Models\FieldGroup;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class FieldGroupController extends Controller
{
    public function index()
    {
        $groups = FieldGroup::withCount(['fields', 'contentTypes'])
            ->orderBy('label')
            ->get();

        return view('cms::admin.field-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('cms::admin.field-groups.form', [
            'group' => new FieldGroup(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $group = FieldGroup::create($data);

        return redirect()->route('cms.admin.field-groups.edit', $group->id)
            ->with('success', 'Field group created. Add fields below.');
    }

    public function edit(int $id)
    {
        $group = FieldGroup::with('fields')->findOrFail($id);

        return view('cms::admin.field-groups.form', compact('group'));
    }

    public function update(Request $request, int $id)
    {
        $group = FieldGroup::findOrFail($id);
        $group->update($this->validated($request, $group->id));

        return redirect()->route('cms.admin.field-groups.edit', $group->id)
            ->with('success', 'Field group updated.');
    }

    public function destroy(int $id)
    {
        FieldGroup::where('id', $id)->delete();

        return redirect()->route('cms.admin.field-groups.index')
            ->with('success', 'Field group deleted.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'key'         => 'required|string|max:100|regex:/^[a-z0-9\-_]+$/|unique:field_groups,key' . ($ignoreId ? ',' . $ignoreId : ''),
            'label'       => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
        ], [
            'key.regex' => 'Key must be lowercase letters, numbers, dashes, or underscores only.',
        ]);

        $data['key'] = Str::lower(trim($data['key']));

        return $data;
    }
}
