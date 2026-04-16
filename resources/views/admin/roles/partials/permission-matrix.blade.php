{{--
 | Contensio - The open content platform for Laravel.
 | Admin — roles: permission matrix.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

{{--
    Renders the grouped permission matrix used by create + edit role forms.
    Variables:
        $permissions — Collection<Permission> grouped by module
        $assignedIds — int[]  (ids currently granted to this role)
--}}

@php
    $moduleLabels = [
        'system'    => 'System',
        'dashboard' => 'Dashboard',
        'content'   => 'Content',
        'media'     => 'Media',
        'taxonomy'  => 'Taxonomies & Terms',
        'menu'      => 'Menus',
        'theme'     => 'Themes',
        'plugin'    => 'Plugins',
        'settings'  => 'Settings',
        'users'     => 'Users & Roles',
        'activity'  => 'Activity log',
    ];
    $moduleIcons = [
        'system'    => 'bi-shield',
        'dashboard' => 'bi-speedometer2',
        'content'   => 'bi-file-text',
        'media'     => 'bi-image',
        'taxonomy'  => 'bi-tags',
        'menu'      => 'bi-list',
        'theme'     => 'bi-palette',
        'plugin'    => 'bi-plug',
        'settings'  => 'bi-gear',
        'users'     => 'bi-people',
        'activity'  => 'bi-activity',
    ];
@endphp

<div class="space-y-4">
    @foreach($permissions as $module => $perms)
    @php
        // The wildcard '*' is an internal permission for super_admin only — never shown in the UI
        $visiblePerms = $perms->filter(fn($p) => $p->name !== '*');
        $label = $moduleLabels[$module] ?? ucfirst($module);
        $icon  = $moduleIcons[$module]  ?? 'bi-folder';
    @endphp
    @if($visiblePerms->isEmpty()) @continue @endif
    <div class="border border-gray-200 rounded-lg overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-2.5 bg-gray-50 border-b border-gray-200">
            <i class="bi {{ $icon }} text-gray-500"></i>
            <h3 class="text-sm font-bold text-gray-800">{{ $label }}</h3>
        </div>
        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
            @foreach($visiblePerms as $perm)
            <label class="flex items-start gap-2.5 cursor-pointer p-2 rounded-lg hover:bg-gray-50 transition-colors">
                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                       {{ in_array($perm->id, (array) old('permissions', $assignedIds ?? [])) ? 'checked' : '' }}
                       class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-ember-500 shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 font-mono">{{ $perm->name }}</p>
                    @if($perm->description)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $perm->description }}</p>
                    @endif
                    @if($perm->plugin_name)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 mt-1">
                        {{ $perm->plugin_name }}
                    </span>
                    @endif
                </div>
            </label>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
