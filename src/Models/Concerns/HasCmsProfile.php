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

namespace Contensio\Cms\Models\Concerns;

use Contensio\Cms\Models\Language;
use Contensio\Cms\Models\Role;
use Contensio\Cms\Models\UserMeta;
use Contensio\Cms\Support\AccessControl;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Fortify\TwoFactorAuthenticatable;

trait HasCmsProfile
{
    use TwoFactorAuthenticatable;


    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function meta(): HasMany
    {
        return $this->hasMany(UserMeta::class);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    /**
     * True if this user has the Administrator role (or legacy super_admin).
     */
    public function isAdministrator(): bool
    {
        return $this->hasRole(AccessControl::ROLE_ADMINISTRATOR)
            || $this->hasRole('super_admin'); // legacy fallback
    }

    /**
     * @deprecated use isAdministrator() — kept for backwards compatibility
     */
    public function isSuperAdmin(): bool
    {
        return $this->isAdministrator();
    }

    /**
     * Check if this user has the given permission.
     *
     * The special `*` permission grants everything.
     *
     * If $contentTypeId is provided, the permission check is scoped to that
     * content type: a role_permission pivot row with content_type_id=NULL
     * (all types) or matching the provided id grants access. If no type is
     * provided, any matching pivot row grants access.
     *
     * @param string   $permissionName  e.g. "content.update.any", "themes.install"
     * @param int|null $contentTypeId   optional — scope the check to a specific content type
     */
    public function hasPermission(string $permissionName, ?int $contentTypeId = null): bool
    {
        // Eager-load permissions with pivot if not already loaded
        $roles = $this->relationLoaded('roles')
            ? $this->roles
            : $this->roles()->with('permissions')->get();

        foreach ($roles as $role) {
            foreach ($role->permissions as $permission) {
                // Super-permission — grants everything
                if ($permission->name === AccessControl::PERMISSION_ALL) {
                    return true;
                }

                if ($permission->name !== $permissionName) {
                    continue;
                }

                // Exact name match — now check type scope
                $pivotTypeId = $permission->pivot->content_type_id ?? null;

                // NULL pivot = all content types (or non-content permission)
                if ($pivotTypeId === null) {
                    return true;
                }

                // Type-scoped permission — caller didn't specify type: treat as allowed
                // if *any* scoped grant exists (the user has it for at least one type)
                if ($contentTypeId === null) {
                    return true;
                }

                // Caller asked about a specific type — require exact scope match
                if ((int) $pivotTypeId === $contentTypeId) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return a flat list of permission names this user has (for admin UI display).
     * Expanded view — includes '*' if present. Does not resolve per-type scopes.
     */
    public function allPermissionNames(): array
    {
        $roles = $this->relationLoaded('roles')
            ? $this->roles
            : $this->roles()->with('permissions')->get();

        return $roles
            ->flatMap(fn ($r) => $r->permissions->pluck('name'))
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * True if user has at least one role — can enter the admin panel.
     */
    public function canAccessAdmin(): bool
    {
        return $this->roles()->exists();
    }
}
