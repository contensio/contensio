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

use App\Models\User;
use Contensio\Cms\Models\Language;
use Contensio\Cms\Models\Role;
use Contensio\Cms\Models\RoleTranslation;
use Contensio\Cms\Support\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles.translations'])->latest()->get();
        $defaultLangId = Language::where('is_default', true)->value('id');

        return view('cms::admin.users.index', compact('users', 'defaultLangId'));
    }

    public function create()
    {
        $roles         = Role::with('translations')->orderBy('position')->orderBy('name')->get();
        $defaultLangId = Language::where('is_default', true)->value('id');

        return view('cms::admin.users.create', compact('roles', 'defaultLangId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles'    => ['array'],
            'roles.*'  => ['integer', 'exists:roles,id'],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->roles()->sync($data['roles'] ?? []);
        });

        return redirect()
            ->route('cms.admin.users.index')
            ->with('success', 'User created.');
    }

    public function edit(int $id)
    {
        $user          = User::with('roles')->findOrFail($id);
        $roles         = Role::with('translations')->orderBy('position')->orderBy('name')->get();
        $defaultLangId = Language::where('is_default', true)->value('id');
        $assignedRoles = $user->roles->pluck('id')->toArray();

        return view('cms::admin.users.edit', compact('user', 'roles', 'defaultLangId', 'assignedRoles'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles'    => ['array'],
            'roles.*'  => ['integer', 'exists:roles,id'],
        ]);

        DB::transaction(function () use ($data, $user) {
            $user->name  = $data['name'];
            $user->email = $data['email'];
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            // Prevent removing the last Administrator's role
            $newRoles = $data['roles'] ?? [];
            $this->guardLastAdministrator($user, $newRoles);

            $user->roles()->sync($newRoles);
        });

        return redirect()
            ->route('cms.admin.users.edit', $user->id)
            ->with('success', 'User updated.');
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $this->guardLastAdministrator($user, []);

        $user->roles()->detach();
        $user->delete();

        return redirect()
            ->route('cms.admin.users.index')
            ->with('success', 'User deleted.');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Prevent removing the last Administrator. If this user has the admin role
     * and the new role set would drop it, we check there's at least one other
     * admin remaining.
     */
    protected function guardLastAdministrator(User $user, array $newRoleIds): void
    {
        $adminRoleId = Role::where('name', AccessControl::ROLE_ADMINISTRATOR)->value('id');
        if (! $adminRoleId) {
            return;
        }

        $userHasAdmin = $user->roles->contains('id', $adminRoleId);
        $willHaveAdmin = in_array($adminRoleId, array_map('intval', $newRoleIds), true);

        if ($userHasAdmin && ! $willHaveAdmin) {
            $otherAdmins = User::whereHas('roles', fn ($q) => $q->where('roles.id', $adminRoleId))
                ->where('id', '!=', $user->id)
                ->count();
            if ($otherAdmins === 0) {
                abort(422, 'Cannot remove the Administrator role from the last remaining administrator.');
            }
        }
    }
}
