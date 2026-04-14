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

use Contensio\Cms\Models\Permission;
use Contensio\Cms\Models\Role;
use Contensio\Cms\Models\RoleTranslation;
use Illuminate\Support\Facades\DB;

/**
 * Central source of truth for Contensio's access-control model.
 *
 * Defines the core permission catalog and the four predefined roles,
 * and provides idempotent sync methods used by:
 *   - Fresh install (seeds the DB with defaults)
 *   - Plugin enable (adds plugin-declared permissions / roles)
 *   - Plugin disable / uninstall (opt-in cleanup)
 *
 * Permission naming convention: `{module}.{action}[.{scope}]`
 *   - `content.view`, `content.update.own`, `content.update.any`
 *   - `media.upload`, `media.delete.any`
 *   - `themes.activate`, `plugins.enable`, etc.
 *
 * Content permissions can be **scoped to a specific content type** via the
 * role_permissions.content_type_id pivot column. A pivot row with
 * content_type_id = NULL means "all content types."
 *
 * The special `*` permission grants everything (used by Administrator).
 */
class AccessControl
{
    // ── Core role names (stable identifiers) ─────────────────────────────

    public const ROLE_ADMINISTRATOR = 'administrator';
    public const ROLE_EDITOR        = 'editor';
    public const ROLE_AUTHOR        = 'author';
    public const ROLE_VIEWER        = 'viewer';

    // ── Special permissions ──────────────────────────────────────────────

    public const PERMISSION_ALL = '*';

    /**
     * Core permission catalog. Seeded on install; safe to run repeatedly.
     *
     * Shape: module => [ name => description ]
     */
    public static function corePermissions(): array
    {
        return [
            // Catch-all — only granted to Administrator
            'system' => [
                '*' => 'Full access to all permissions (super admin)',
            ],

            'dashboard' => [
                'dashboard.view' => 'View the admin dashboard',
            ],

            'content' => [
                'content.view'          => 'View any content in admin lists',
                'content.create'        => 'Create new content',
                'content.update.own'    => 'Edit content authored by self',
                'content.update.any'    => 'Edit any content regardless of author',
                'content.publish.own'   => 'Publish own content',
                'content.publish.any'   => 'Publish any content',
                'content.delete.own'    => 'Delete own content',
                'content.delete.any'    => 'Delete any content',
            ],

            'media' => [
                'media.upload'      => 'Upload media files',
                'media.delete.own'  => 'Delete own uploads',
                'media.delete.any'  => 'Delete any media file',
            ],

            'taxonomy' => [
                'taxonomies.manage' => 'Create, edit, delete taxonomies',
                'terms.manage'      => 'Create, edit, delete terms within taxonomies',
            ],

            'menu' => [
                'menus.manage' => 'Build and edit navigation menus',
            ],

            'theme' => [
                'themes.activate'   => 'Activate a different theme',
                'themes.install'    => 'Upload and install new themes',
                'themes.customize'  => 'Edit the active theme\'s settings',
                'themes.uninstall'  => 'Delete installed themes',
            ],

            'plugin' => [
                'plugins.enable'    => 'Enable installed plugins',
                'plugins.install'   => 'Upload and install new plugins',
                'plugins.configure' => 'Configure plugin settings',
                'plugins.uninstall' => 'Delete installed plugins',
            ],

            'settings' => [
                'settings.manage'        => 'Edit general site settings',
                'languages.manage'       => 'Add, remove, configure languages',
                'content_types.manage'   => 'Define custom content types',
            ],

            'users' => [
                'users.view'     => 'View user list',
                'users.create'   => 'Create new users',
                'users.update'   => 'Edit existing users',
                'users.delete'   => 'Delete users',
                'roles.manage'   => 'Create, edit, delete roles and assign permissions',
            ],

            'activity' => [
                'activity_log.view' => 'View the site-wide activity log',
            ],
        ];
    }

    /**
     * Core role definitions. Each role lists the permissions granted.
     * Installed idempotently on first boot.
     */
    public static function coreRoles(): array
    {
        return [
            self::ROLE_ADMINISTRATOR => [
                'label'       => 'Administrator',
                'description' => 'Full control over everything. Manage users, themes, plugins, and all content.',
                'position'    => 10,
                'permissions' => [self::PERMISSION_ALL],
            ],

            self::ROLE_EDITOR => [
                'label'       => 'Editor',
                'description' => 'Manages all content on the site. Cannot manage users or site settings.',
                'position'    => 20,
                'permissions' => [
                    'dashboard.view',
                    'content.view',
                    'content.create',
                    'content.update.own',
                    'content.update.any',
                    'content.publish.own',
                    'content.publish.any',
                    'content.delete.own',
                    'content.delete.any',
                    'media.upload',
                    'media.delete.own',
                    'media.delete.any',
                    'taxonomies.manage',
                    'terms.manage',
                    'menus.manage',
                    'activity_log.view',
                ],
            ],

            self::ROLE_AUTHOR => [
                'label'       => 'Author',
                'description' => 'Writes and publishes own content. Cannot edit others\' work or manage the site.',
                'position'    => 30,
                'permissions' => [
                    'dashboard.view',
                    'content.view',
                    'content.create',
                    'content.update.own',
                    'content.publish.own',
                    'content.delete.own',
                    'media.upload',
                    'media.delete.own',
                ],
            ],

            self::ROLE_VIEWER => [
                'label'       => 'Viewer',
                'description' => 'Read-only admin access. Cannot edit anything. Useful for clients and stakeholders.',
                'position'    => 40,
                'permissions' => [
                    'dashboard.view',
                    'content.view',
                    'activity_log.view',
                ],
            ],
        ];
    }

    // ── Sync methods ─────────────────────────────────────────────────────

    /**
     * True if the role / permission system has been seeded.
     */
    public static function isSeeded(): bool
    {
        try {
            return Permission::count() > 0 && Role::count() > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Seed the core permission catalog and the four predefined roles.
     * Idempotent: running twice won't duplicate anything.
     */
    public static function seedCoreDefaults(): void
    {
        DB::transaction(function () {
            self::syncPermissions(self::corePermissions(), pluginName: null);
            self::syncRoles(self::coreRoles(), pluginName: null, isSystem: true);
        });
    }

    /**
     * Sync a catalog of permissions into the DB (used by install + plugins).
     * Shape: [module => [name => description]]
     */
    public static function syncPermissions(array $catalog, ?string $pluginName = null): void
    {
        foreach ($catalog as $module => $entries) {
            foreach ($entries as $name => $description) {
                Permission::updateOrCreate(
                    ['name' => $name],
                    [
                        'module'      => $module,
                        'description' => $description,
                        'plugin_name' => $pluginName,
                    ]
                );
            }
        }
    }

    /**
     * Sync a set of roles into the DB.
     * Shape: [roleName => ['label', 'description', 'position', 'permissions' => [permNames]]]
     */
    public static function syncRoles(array $roles, ?string $pluginName = null, bool $isSystem = false): void
    {
        foreach ($roles as $name => $config) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                [
                    'is_system'   => $isSystem,
                    'plugin_name' => $pluginName,
                    'position'    => $config['position'] ?? 100,
                ]
            );

            // Default-language translation (only insert on create; admins may later edit labels)
            $defaultLang = \Contensio\Cms\Models\Language::where('is_default', true)->value('id')
                ?? \Contensio\Cms\Models\Language::orderBy('position')->value('id');
            if ($defaultLang && ! $role->translations()->where('language_id', $defaultLang)->exists()) {
                RoleTranslation::create([
                    'role_id'     => $role->id,
                    'language_id' => $defaultLang,
                    'labels'      => [
                        'title'       => $config['label']       ?? ucfirst($name),
                        'description' => $config['description'] ?? '',
                    ],
                ]);
            }

            // Permission assignment (syncs role_permissions pivot)
            if (isset($config['permissions'])) {
                $permissionIds = Permission::whereIn('name', $config['permissions'])->pluck('id');
                $sync = [];
                foreach ($permissionIds as $pid) {
                    $sync[$pid] = ['content_type_id' => null]; // null = all content types
                }
                $role->permissions()->sync($sync);
            }
        }
    }

    /**
     * Remove permissions + roles declared by a specific plugin.
     * Called during plugin uninstall (opt-in).
     */
    public static function removePluginDefinitions(string $pluginName): void
    {
        DB::transaction(function () use ($pluginName) {
            Role::where('plugin_name', $pluginName)->delete();
            Permission::where('plugin_name', $pluginName)->delete();
        });
    }
}
