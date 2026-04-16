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

use Contensio\Cms\Models\Language;
use Contensio\Cms\Models\Permission;
use Contensio\Cms\Models\Role;
use Contensio\Cms\Models\RoleTranslation;
use Contensio\Cms\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles         = Role::with(['translations', 'permissions'])
            ->withCount('users')
            ->orderBy('position')
            ->orderBy('name')
            ->get();
        $defaultLangId = Language::where('is_default', true)->value('id');

        return view('cms::admin.roles.index', compact('roles', 'defaultLangId'));
    }

    public function create()
    {
        $permissions   = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $defaultLangId = Language::where('is_default', true)->value('id');

        return view('cms::admin.roles.create', compact('permissions', 'defaultLangId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'         => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:500'],
            'permissions'   => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $createdRoleId = null;
        DB::transaction(function () use ($data, &$createdRoleId) {
            $name = $this->uniqueSlug($data['label']);

            $role = Role::create([
                'name'        => $name,
                'is_system'   => false,
                'plugin_name' => null,
                'position'    => 100,
            ]);
            $createdRoleId = $role->id;

            $langId = Language::where('is_default', true)->value('id')
                ?? Language::orderBy('position')->value('id');
            if ($langId) {
                RoleTranslation::create([
                    'role_id'     => $role->id,
                    'language_id' => $langId,
                    'labels'      => [
                        'title'       => $data['label'],
                        'description' => $data['description'] ?? '',
                    ],
                ]);
            }

            $sync = [];
            foreach ($data['permissions'] ?? [] as $pid) {
                $sync[$pid] = ['content_type_id' => null];
            }
            $role->permissions()->sync($sync);
        });

        Activity::record('created', 'role', $createdRoleId, "Role: {$data['label']}")
            ->withProperties(['permissions_count' => count($data['permissions'] ?? [])]);

        return redirect()
            ->route('cms.admin.roles.index')
            ->with('success', 'Role created.');
    }

    public function edit(int $id)
    {
        $role          = Role::with(['translations', 'permissions'])->findOrFail($id);
        $permissions   = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $defaultLangId = Language::where('is_default', true)->value('id');
        $assignedIds   = $role->permissions->pluck('id')->toArray();

        return view('cms::admin.roles.edit', compact('role', 'permissions', 'defaultLangId', 'assignedIds'));
    }

    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $data = $request->validate([
            'label'         => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:500'],
            'permissions'   => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        DB::transaction(function () use ($role, $data) {
            // Update translation for default language (admin-edited name/description)
            $langId = Language::where('is_default', true)->value('id')
                ?? Language::orderBy('position')->value('id');
            if ($langId) {
                RoleTranslation::updateOrCreate(
                    ['role_id' => $role->id, 'language_id' => $langId],
                    ['labels' => [
                        'title'       => $data['label'],
                        'description' => $data['description'] ?? '',
                    ]]
                );
            }

            // Re-sync permissions
            $sync = [];
            foreach ($data['permissions'] ?? [] as $pid) {
                $sync[$pid] = ['content_type_id' => null];
            }
            $role->permissions()->sync($sync);
        });

        Activity::record('updated', 'role', $role->id, "Role: {$data['label']}")
            ->withProperties(['permissions_count' => count($data['permissions'] ?? [])]);

        return redirect()
            ->route('cms.admin.roles.edit', $role->id)
            ->with('success', 'Role updated.');
    }

    public function destroy(int $id)
    {
        $role = Role::withCount('users')->findOrFail($id);

        if ($role->is_system) {
            return back()->withErrors(['role' => 'Core roles cannot be deleted. You can edit their permissions instead.']);
        }

        if ($role->plugin_name) {
            return back()->withErrors(['role' => 'This role was added by a plugin. Uninstall the plugin to remove it.']);
        }

        if ($role->users_count > 0) {
            return back()->withErrors(['role' => 'This role is assigned to ' . $role->users_count . ' user(s). Reassign them first, then delete the role.']);
        }

        $roleName = $role->name;
        $role->delete();

        Activity::record('deleted', 'role', $id, "Role: {$roleName}");

        return redirect()
            ->route('cms.admin.roles.index')
            ->with('success', 'Role deleted.');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Slugify a label and guarantee uniqueness against existing role names.
     */
    protected function uniqueSlug(string $label): string
    {
        $base = Str::slug($label, '_');
        if ($base === '') {
            $base = 'role';
        }
        $base = substr($base, 0, 45);

        $slug    = $base;
        $counter = 2;
        while (Role::where('name', $slug)->exists()) {
            $slug = $base . '_' . $counter++;
        }
        return $slug;
    }
}
