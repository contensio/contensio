{{--
 | Contensio - The open content platform for Laravel.
 | Admin — users & registration settings.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('contensio::admin.layout')

@section('title', 'Users & Registration')

@section('breadcrumb')
<a href="{{ route('contensio.account.settings.index') }}" class="text-gray-400 hover:text-gray-700">Configuration</a>
<span class="mx-2 text-gray-300">/</span>
<span class="font-medium text-gray-700">Users &amp; Registration</span>
@endsection

@section('content')

<div>

    <h1 class="text-xl font-bold text-gray-900 mb-1">Users &amp; Registration</h1>
    <p class="text-sm text-gray-500 mb-5">Control user account behaviour and registration options.</p>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @php
        // Helper: toggle state from settings collection (default 'on' if $default=true)
        $toggle = fn(string $key, bool $default = false) =>
            (($settings[$key] ?? ($default ? '1' : '')) === '1') ? 'true' : 'false';
    @endphp

    <form method="POST" action="{{ route('contensio.account.settings.users.save') }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

            {{-- ═══════════ LEFT: Registration ═══════════ --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-5">
                <div>
                    <h2 class="text-base font-bold text-gray-900 mb-1">Registration</h2>
                    <p class="text-xs text-gray-500">Control how new accounts are created.</p>
                </div>

                {{-- Disable registration --}}
                @include('contensio::admin.settings.partials.toggle', [
                    'name'        => 'registration_disabled',
                    'state'       => $toggle('registration_disabled'),
                    'label'       => 'Disable registration',
                    'description' => 'When enabled, the public registration page shows a closed notice. Administrators can still create users.',
                ])

                <div class="border-t border-gray-100"></div>

                {{-- Require admin approval --}}
                @include('contensio::admin.settings.partials.toggle', [
                    'name'        => 'require_approval',
                    'state'       => $toggle('require_approval'),
                    'label'       => 'Require admin approval',
                    'description' => 'New registrations are inactive until an administrator manually activates them.',
                ])

                <div class="border-t border-gray-100"></div>

                {{-- Default role --}}
                <div>
                    <label class="block text-sm font-medium text-gray-800 mb-1.5">Default role for new registrations</label>
                    <select name="default_registration_role_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent bg-white">
                        <option value="">— No default role —</option>
                        @foreach($roles as $role)
                        @php $roleLabel = $role->translations->first()?->label ?? ucfirst($role->name); @endphp
                        <option value="{{ $role->id }}"
                                {{ ($settings['default_registration_role_id'] ?? '') == $role->id ? 'selected' : '' }}>
                            {{ $roleLabel }}
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Automatically assigned when a new user registers. Leave blank to create users with no role.</p>
                </div>

                <div class="border-t border-gray-100"></div>

                {{-- Disable email verification --}}
                @include('contensio::admin.settings.partials.toggle', [
                    'name'        => 'email_verification_disabled',
                    'state'       => $toggle('email_verification_disabled'),
                    'label'       => 'Disable email verification',
                    'description' => 'New accounts are activated immediately without verifying the email address.',
                ])

                <div class="flex items-start gap-2.5 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3">
                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        <strong class="font-semibold">Strongly not recommended.</strong>
                        Skipping email verification makes it trivial for bots to create fake accounts and abuse comments, contact forms, and other features.
                    </p>
                </div>
            </div>

            {{-- ═══════════ RIGHT: stacked cards ═══════════ --}}
            <div class="space-y-6">

                {{-- Accounts --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Accounts</h2>
                        <p class="text-xs text-gray-500">What users can do with their own account.</p>
                    </div>

                    @include('contensio::admin.settings.partials.toggle', [
                        'name'        => 'allow_email_change',
                        'state'       => $toggle('allow_email_change', true),
                        'label'       => 'Allow users to change their email',
                        'description' => 'When disabled, only administrators can update a user\'s email address.',
                    ])

                    <div class="border-t border-gray-100"></div>

                    @include('contensio::admin.settings.partials.toggle', [
                        'name'        => 'allow_account_deletion',
                        'state'       => $toggle('allow_account_deletion'),
                        'label'       => 'Allow users to delete their own account',
                        'description' => 'Users will see a "Delete account" option in their profile requiring password confirmation.',
                    ])
                </div>

                {{-- Profile --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Profile</h2>
                        <p class="text-xs text-gray-500">Which profile fields users can manage themselves.</p>
                    </div>

                    @include('contensio::admin.settings.partials.toggle', [
                        'name'        => 'allow_bio',
                        'state'       => $toggle('allow_bio', true),
                        'label'       => 'Allow users to set a bio',
                        'description' => 'When disabled, the bio field is hidden from the profile page.',
                    ])

                    <div class="border-t border-gray-100"></div>

                    @include('contensio::admin.settings.partials.toggle', [
                        'name'        => 'allow_avatar',
                        'state'       => $toggle('allow_avatar', true),
                        'label'       => 'Allow users to upload an avatar',
                        'description' => 'When disabled, the avatar upload control is hidden. Administrators can still set avatars.',
                    ])
                </div>

                {{-- Usernames --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Usernames</h2>
                        <p class="text-xs text-gray-500">Username editing rules.</p>
                    </div>

                    @include('contensio::admin.settings.partials.toggle', [
                        'name'        => 'users_can_change_username',
                        'state'       => $toggle('users_can_change_username'),
                        'label'       => 'Users can change their username',
                        'description' => 'When disabled, only administrators can change a user\'s username.',
                    ])

                    <div class="border-t border-gray-100"></div>

                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1.5">Username change cooldown</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="username_cooldown_days"
                                   value="{{ old('username_cooldown_days', $settings['username_cooldown_days'] ?? 0) }}"
                                   min="0" max="3650"
                                   class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm text-center
                                          focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                            <span class="text-sm text-gray-600">days between changes</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Set to <code class="bg-gray-100 px-1 rounded">0</code> for no cooldown. Prevents frequent username changes and impersonation.</p>
                    </div>
                </div>

                {{-- Security --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 mb-1">Security</h2>
                        <p class="text-xs text-gray-500">Session and inactivity controls.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1.5">Force logout after inactivity</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="inactivity_logout_days"
                                   value="{{ old('inactivity_logout_days', $settings['inactivity_logout_days'] ?? 0) }}"
                                   min="0" max="3650"
                                   class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm text-center
                                          focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                            <span class="text-sm text-gray-600">days of inactivity</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Set to <code class="bg-gray-100 px-1 rounded">0</code> to never force logout.</p>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1.5">Max active sessions per user</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="max_sessions"
                                   value="{{ old('max_sessions', $settings['max_sessions'] ?? 0) }}"
                                   min="0" max="100"
                                   class="w-24 rounded-lg border border-gray-300 px-3 py-2 text-sm text-center
                                          focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                            <span class="text-sm text-gray-600">sessions</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Set to <code class="bg-gray-100 px-1 rounded">0</code> for unlimited. When exceeded, the oldest session is signed out automatically.</p>
                    </div>
                </div>

            </div>{{-- /right column --}}
        </div>

        <div class="flex justify-start mt-6">
            <button type="submit"
                    class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                Save Changes
            </button>
        </div>
    </form>

</div>

@endsection
