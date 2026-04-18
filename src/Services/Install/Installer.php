<?php

/**
 * Contensio - The open content platform for Laravel.
 * Shared install logic: seeds languages, settings, content types, taxonomies,
 * roles, permissions, block types, and creates the first admin user.
 *
 * Used by:
 *   - Web installer (Http\Controllers\Install\InstallController)
 *   - CLI installer (Console\Commands\InstallCommand)
 *
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

namespace Contensio\Services\Install;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Installer
{
    public function __construct(protected EnvWriter $env)
    {
    }

    /**
     * Shortcut list of UI languages Contensio ships with translations for.
     * CLI installer uses this for the language prompt; web installer exposes
     * the same list on the website-settings form.
     *
     * @return array<string, string>  code => human label
     */
    public function availableLanguages(): array
    {
        return [
            'en' => 'English',
            'ro' => 'Romanian',
            'de' => 'German',
            'es' => 'Spanish',
            'fr' => 'French',
            'it' => 'Italian',
            'pt' => 'Portuguese',
        ];
    }

    /**
     * Ensure a language row exists and return its ID. Marks the first-seeded
     * language as the site default.
     */
    public function seedLanguage(string $code): int
    {
        $name = $this->availableLanguages()[$code] ?? $code;

        DB::table('languages')->insertOrIgnore([
            'code'       => $code,
            'name'       => $name,
            'is_default' => true,
            'status'     => 'active',
            'direction'  => in_array($code, ['ar', 'he', 'fa', 'ur'], true) ? 'rtl' : 'ltr',
            'position'   => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('languages')->where('code', $code)->value('id');
    }

    public function seedSettings(string $siteName, int $languageId): void
    {
        $settings = [
            ['module' => 'core', 'setting_key' => 'site_name',    'value' => $siteName, 'is_translatable' => true],
            ['module' => 'core', 'setting_key' => 'site_tagline', 'value' => '',         'is_translatable' => true],
            ['module' => 'core', 'setting_key' => 'timezone',     'value' => 'UTC',      'is_translatable' => false],
            ['module' => 'core', 'setting_key' => 'date_format',  'value' => 'Y-m-d',    'is_translatable' => false],
            ['module' => 'core', 'setting_key' => 'time_format',  'value' => 'H:i',      'is_translatable' => false],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['module' => $setting['module'], 'setting_key' => $setting['setting_key']],
                array_merge($setting, ['updated_at' => now()])
            );

            if ($setting['is_translatable']) {
                $id = DB::table('settings')
                    ->where('module', $setting['module'])
                    ->where('setting_key', $setting['setting_key'])
                    ->value('id');

                DB::table('setting_translations')->updateOrInsert(
                    ['setting_id' => $id, 'language_id' => $languageId],
                    ['value' => $setting['value']]
                );
            }
        }
    }

    public function seedContentTypes(int $languageId): void
    {
        $types = [
            [
                'name'               => 'page',
                'icon'               => 'document',
                'has_slug'           => true,
                'has_editor'         => true,
                'has_excerpt'        => false,
                'has_featured_image' => false,
                'has_categories'     => false,
                'has_tags'           => false,
                'has_comments'       => false,
                'has_seo'            => true,
                'has_autosave'       => true,
                'is_hierarchical'    => true,
                'is_system'          => true,
                'position'           => 1,
                'labels' => [
                    'singular'  => 'Page',    'plural' => 'Pages', 'create' => 'Add New Page',
                    'edit'      => 'Edit Page','delete' => 'Delete Page', 'all' => 'All Pages',
                    'search'    => 'Search Pages', 'not_found' => 'No pages found',
                ],
                'slug' => 'page',
            ],
            [
                'name'               => 'post',
                'icon'               => 'pencil',
                'has_slug'           => true,
                'has_editor'         => true,
                'has_excerpt'        => true,
                'has_featured_image' => true,
                'has_categories'     => true,
                'has_tags'           => true,
                'has_comments'       => true,
                'has_seo'            => true,
                'has_autosave'       => true,
                'is_hierarchical'    => false,
                'is_system'          => true,
                'position'           => 2,
                'labels' => [
                    'singular'  => 'Post',    'plural' => 'Posts', 'create' => 'Add New Post',
                    'edit'      => 'Edit Post','delete' => 'Delete Post', 'all' => 'All Posts',
                    'search'    => 'Search Posts', 'not_found' => 'No posts found',
                ],
                'slug' => 'blog',
            ],
        ];

        foreach ($types as $type) {
            $labels = $type['labels'];
            $slug   = $type['slug'];
            unset($type['labels'], $type['slug']);

            if (DB::table('content_types')->where('name', $type['name'])->exists()) continue;

            $type['created_at'] = now();
            $type['updated_at'] = now();
            $id = DB::table('content_types')->insertGetId($type);

            DB::table('content_type_translations')->insert([
                'content_type_id' => $id,
                'language_id'     => $languageId,
                'slug'            => $slug,
                'labels'          => json_encode($labels),
            ]);
        }
    }

    public function seedTaxonomies(int $languageId): void
    {
        $taxonomies = [
            [
                'name' => 'category', 'is_hierarchical' => true, 'is_system' => true,
                'labels' => ['singular' => 'Category', 'plural' => 'Categories', 'create' => 'Add New Category', 'all' => 'All Categories', 'not_found' => 'No categories found'],
                'slug'   => 'category',
            ],
            [
                'name' => 'tag', 'is_hierarchical' => false, 'is_system' => true,
                'labels' => ['singular' => 'Tag', 'plural' => 'Tags', 'create' => 'Add New Tag', 'all' => 'All Tags', 'not_found' => 'No tags found'],
                'slug'   => 'tag',
            ],
        ];

        foreach ($taxonomies as $taxonomy) {
            $labels = $taxonomy['labels'];
            $slug   = $taxonomy['slug'];
            unset($taxonomy['labels'], $taxonomy['slug']);

            $postTypeId = DB::table('content_types')->where('name', 'post')->value('id');

            if (DB::table('taxonomies')->where('name', $taxonomy['name'])->exists()) {
                $existingTaxId = DB::table('taxonomies')->where('name', $taxonomy['name'])->value('id');
                if ($postTypeId && $existingTaxId) {
                    DB::table('content_type_taxonomies')->insertOrIgnore([
                        'content_type_id' => $postTypeId,
                        'taxonomy_id'     => $existingTaxId,
                    ]);
                }
                continue;
            }

            $taxonomy['created_at'] = now();
            $taxonomy['updated_at'] = now();
            $id = DB::table('taxonomies')->insertGetId($taxonomy);

            DB::table('taxonomy_translations')->insert([
                'taxonomy_id' => $id,
                'language_id' => $languageId,
                'slug'        => $slug,
                'labels'      => json_encode($labels),
            ]);

            if ($postTypeId) {
                DB::table('content_type_taxonomies')->insertOrIgnore([
                    'content_type_id' => $postTypeId,
                    'taxonomy_id'     => $id,
                ]);
            }
        }
    }

    /** Returns the super-admin role ID. */
    public function seedRolesAndPermissions(int $languageId): int
    {
        $roles = [
            ['name' => 'super_admin', 'is_system' => true, 'position' => 1, 'label' => 'Super Admin', 'description' => 'Full access to everything'],
            ['name' => 'admin',       'is_system' => true, 'position' => 2, 'label' => 'Admin',       'description' => 'Manages content and users'],
            ['name' => 'editor',      'is_system' => true, 'position' => 3, 'label' => 'Editor',      'description' => 'Creates and edits own content'],
        ];

        $roleIds = [];
        foreach ($roles as $role) {
            $label = $role['label']; $description = $role['description'];
            unset($role['label'], $role['description']);

            $existing = DB::table('roles')->where('name', $role['name'])->first();
            if ($existing) { $roleIds[$role['name']] = $existing->id; continue; }

            $role['created_at'] = now();
            $role['updated_at'] = now();
            $id = DB::table('roles')->insertGetId($role);
            $roleIds[$role['name']] = $id;

            DB::table('role_translations')->insert([
                'role_id'     => $id,
                'language_id' => $languageId,
                'labels'      => json_encode(['title' => $label, 'description' => $description]),
            ]);
        }

        $permissions = [
            ['module' => 'content', 'name' => 'content.view'],
            ['module' => 'content', 'name' => 'content.create'],
            ['module' => 'content', 'name' => 'content.edit_own'],
            ['module' => 'content', 'name' => 'content.edit_all'],
            ['module' => 'content', 'name' => 'content.publish'],
            ['module' => 'content', 'name' => 'content.delete_own'],
            ['module' => 'content', 'name' => 'content.delete_all'],
            ['module' => 'content', 'name' => 'content.manage_types'],
            ['module' => 'content', 'name' => 'fields.manage'],
            ['module' => 'media',   'name' => 'media.upload'],
            ['module' => 'media',   'name' => 'media.view_all'],
            ['module' => 'media',   'name' => 'media.delete_own'],
            ['module' => 'media',   'name' => 'media.delete_all'],
            ['module' => 'taxonomy','name' => 'taxonomy.manage'],
            ['module' => 'menu',    'name' => 'menu.manage'],
            ['module' => 'seo',     'name' => 'seo.edit_content'],
            ['module' => 'seo',     'name' => 'seo.manage_settings'],
            ['module' => 'seo',     'name' => 'seo.manage_redirects'],
            ['module' => 'users',   'name' => 'users.view'],
            ['module' => 'users',   'name' => 'users.create_editors'],
            ['module' => 'users',   'name' => 'users.create_admins'],
            ['module' => 'users',   'name' => 'users.manage_roles'],
            ['module' => 'users',   'name' => 'users.delete'],
            ['module' => 'comments','name' => 'comments.manage'],
            ['module' => 'system',  'name' => 'system.plugins'],
            ['module' => 'system',  'name' => 'system.themes'],
            ['module' => 'system',  'name' => 'system.settings'],
            ['module' => 'system',  'name' => 'system.languages'],
            ['module' => 'system',  'name' => 'system.backup'],
            ['module' => 'system',  'name' => 'system.updates'],
        ];

        $permissionIds = [];
        foreach ($permissions as $permission) {
            $existing = DB::table('permissions')->where('name', $permission['name'])->first();
            $permissionIds[$permission['name']] = $existing?->id ?? DB::table('permissions')->insertGetId($permission);
        }

        foreach ($permissionIds as $permId) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $roleIds['super_admin'], 'permission_id' => $permId, 'content_type_id' => null,
            ]);
        }

        $adminPermissions = [
            'content.view', 'content.create', 'content.edit_own', 'content.edit_all',
            'content.publish', 'content.delete_own', 'content.delete_all',
            'content.manage_types', 'fields.manage',
            'media.upload', 'media.view_all', 'media.delete_own', 'media.delete_all',
            'taxonomy.manage', 'menu.manage',
            'seo.edit_content', 'seo.manage_settings', 'seo.manage_redirects',
            'users.view', 'users.create_editors', 'users.delete',
            'comments.manage',
        ];
        foreach ($adminPermissions as $perm) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $roleIds['admin'], 'permission_id' => $permissionIds[$perm], 'content_type_id' => null,
            ]);
        }

        $editorPermissions = [
            'content.view', 'content.create', 'content.edit_own', 'content.delete_own',
            'media.upload', 'media.delete_own',
            'seo.edit_content',
        ];
        foreach ($editorPermissions as $perm) {
            DB::table('role_permissions')->insertOrIgnore([
                'role_id' => $roleIds['editor'], 'permission_id' => $permissionIds[$perm], 'content_type_id' => null,
            ]);
        }

        return $roleIds['super_admin'];
    }

    public function seedBlockTypes(): void
    {
        $definitions = config('contensio.blocks', []);

        foreach ($definitions as $name => $def) {
            DB::table('block_types')->updateOrInsert(
                ['name' => $name],
                [
                    'label'       => $def['label']       ?? ucfirst($name),
                    'icon'        => $def['icon']         ?? null,
                    'description' => $def['description']  ?? null,
                    'category'    => $def['category']     ?? 'text',
                    'is_core'     => true,
                    'is_active'   => true,
                    'sort_order'  => $def['sort_order']   ?? 0,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );
        }
    }

    /**
     * Create (or update) the first admin account and assign Super Admin.
     * Returns the user ID.
     */
    public function createAdmin(string $name, string $email, string $password, int $superAdminRoleId, int $languageId): int
    {
        $existing = DB::table('users')->where('email', $email)->first();

        if ($existing) {
            $userId = $existing->id;
            DB::table('users')->where('id', $userId)->update([
                'name'       => $name,
                'password'   => Hash::make($password),
                'is_active'  => true,
                'updated_at' => now(),
            ]);
        } else {
            $userId = DB::table('users')->insertGetId([
                'code'              => (string) random_int(100000000000, 999999999999),
                'name'              => $name,
                'email'             => $email,
                'password'          => Hash::make($password),
                'is_active'         => true,
                'language_id'       => $languageId,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        DB::table('user_roles')->insertOrIgnore([
            'user_id' => $userId,
            'role_id' => $superAdminRoleId,
        ]);

        return (int) $userId;
    }

    /** Full end-to-end seed (CLI path). Skips any step that's already done. */
    public function bootstrap(
        string $siteName,
        string $languageCode,
        string $adminName,
        string $adminEmail,
        string $adminPassword,
    ): int {
        $languageId        = $this->seedLanguage($languageCode);
        $this->seedSettings($siteName, $languageId);
        $this->seedContentTypes($languageId);
        $this->seedTaxonomies($languageId);
        $this->seedBlockTypes();

        $superAdminRoleId = $this->seedRolesAndPermissions($languageId);

        return $this->createAdmin($adminName, $adminEmail, $adminPassword, $superAdminRoleId, $languageId);
    }

    /** Write CONTENSIO_INSTALLED=true to .env. */
    public function markInstalled(): void
    {
        $this->env->set('CONTENSIO_INSTALLED', 'true');
    }
}
