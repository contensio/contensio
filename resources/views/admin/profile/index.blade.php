{{--
 | Contensio - The open content platform for Laravel.
 | Admin — profile page (current user's account + avatar + 2FA).
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('contensio::admin.layout')

@section('title', 'My profile')

@section('breadcrumb')
<span class="font-medium text-gray-700">My profile</span>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>
@endpush

@section('content')

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
    <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

@if(session('status'))
<div class="mb-5 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg px-4 py-3 text-sm">
    {{ session('status') }}
</div>
@endif

@if($errors->any())
<div class="mb-5 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
    @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ═════════════ LEFT COLUMN: Avatar + identity ═════════════ --}}
    <aside class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-6"
             x-data="avatarUploader()">

            {{-- Current avatar --}}
            <div class="flex flex-col items-center text-center">
                @if($user->avatar_path)
                <img src="{{ asset('storage/' . $user->avatar_path) }}"
                     alt="{{ $user->name }}"
                     class="w-32 h-32 rounded-xl object-cover border border-gray-200"
                     id="currentAvatar">
                @else
                <div class="w-32 h-32 rounded-xl bg-ember-500 flex items-center justify-center text-white text-5xl font-bold"
                     id="currentAvatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                @endif

                <h2 class="mt-4 text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>

                @if($canUploadAvatar)
                <div class="mt-5 flex flex-col gap-2 items-center">
                    <button type="button" @click="openPicker()"
                            class="inline-flex items-center justify-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16l4-4a2 2 0 012.828 0L13 15.172M10 14l2-2a2 2 0 012.828 0L16 13m-4-8a2 2 0 11-4 0 2 2 0 014 0zm8-2H6a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"/>
                        </svg>
                        {{ $user->avatar_path ? 'Change avatar' : 'Upload avatar' }}
                    </button>

                    @if($user->avatar_path)
                    <form method="POST" action="{{ route('contensio.account.profile.avatar.remove') }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                                @click.prevent="$dispatch('cms:confirm', {
                                    title: 'Remove avatar?',
                                    description: 'Your avatar will be replaced by the initial of your name.',
                                    confirmLabel: 'Remove',
                                    formId: 'remove-avatar-form'
                                })"
                                class="text-sm text-gray-600 hover:text-red-600 font-medium">
                            Remove avatar
                        </button>
                    </form>
                    <form id="remove-avatar-form" method="POST" action="{{ route('contensio.account.profile.avatar.remove') }}" class="hidden">
                        @csrf @method('DELETE')
                    </form>
                    @endif
                </div>
                @endif

                <input type="file" x-ref="file" class="hidden" accept="image/jpeg,image/png,image/webp"
                       @change="fileChosen($event)">
            </div>

            {{-- ── Cropper modal ──────────────────────────────── --}}
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display: none;">

                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="close()"></div>

                <div x-show="open"
                     class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-auto flex flex-col overflow-hidden">

                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                        <h3 class="text-base font-semibold text-gray-900">Crop your avatar</h3>
                        <button type="button" @click="close()" class="text-gray-400 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="p-5">
                        <div class="bg-gray-50 rounded-lg overflow-hidden" style="max-height: 60vh;">
                            <img x-ref="cropperImg" :src="sourceUrl" alt="" style="max-width: 100%; display: block;">
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <span class="text-xs text-gray-500">Zoom</span>
                            <input type="range" min="-1" max="2" step="0.01" value="0" x-ref="zoom"
                                   @input="setZoom($refs.zoom.value)"
                                   class="flex-1 accent-ember-500">
                            <button type="button" @click="cropper && cropper.reset()"
                                    class="text-xs text-gray-500 hover:text-ember-600">Reset</button>
                        </div>

                        <p class="mt-3 text-xs text-gray-400">
                            Drag the box to reposition. Saved at 512×512, JPEG 85% quality.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-3 bg-gray-50 border-t border-gray-100 shrink-0">
                        <button type="button" @click="close()" class="text-sm text-gray-600 hover:text-gray-900 px-3 py-2">Cancel</button>
                        <button type="button" @click="save()" :disabled="saving"
                                class="bg-ember-500 hover:bg-ember-600 disabled:bg-gray-300 text-white font-semibold text-sm px-5 py-2 rounded-lg transition-colors">
                            <span x-show="!saving">Save avatar</span>
                            <span x-show="saving" x-cloak>Saving…</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </aside>

    {{-- ═════════════ RIGHT COLUMN: Forms ═════════════ --}}
    <section class="lg:col-span-2 space-y-6">

        {{-- Account details --}}
        <form method="POST" action="{{ route('contensio.account.profile.update') }}">
            @csrf @method('PUT')
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h2 class="text-base font-bold text-gray-900">Account details</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        @if($canChangeEmail)
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                        @else
                        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500">
                            {{ $user->email }}
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Email changes are disabled by the administrator.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    @if($canChangeUsername)
                    <div class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-ember-500 focus-within:border-transparent">
                        <span class="inline-flex items-center px-3 bg-gray-50 border-r border-gray-300 text-gray-400 text-sm select-none">@</span>
                        <input type="text" name="username" value="{{ old('username', $user->username ?? '') }}" required
                               pattern="[a-z0-9_]+" title="Only lowercase letters, numbers, and underscores"
                               class="flex-1 px-3 py-2 text-sm outline-none bg-white">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Lowercase letters, numbers, and underscores only.</p>
                    @error('username')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @else
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden bg-gray-50">
                        <span class="inline-flex items-center px-3 border-r border-gray-200 text-gray-400 text-sm select-none">@</span>
                        <span class="flex-1 px-3 py-2 text-sm text-gray-500">{{ $user->username ?? '' }}</span>
                    </div>
                    @if($usernameCooldownEnd)
                    <p class="mt-1 text-xs text-amber-600">You can change your username again on {{ $usernameCooldownEnd->format('M j, Y') }}.</p>
                    @else
                    <p class="mt-1 text-xs text-gray-400">Username changes are disabled by the administrator.</p>
                    @endif
                    @endif
                </div>

                @if($canEditBio)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                    <textarea name="bio" rows="4" placeholder="A short description about yourself…"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent resize-none">{{ old('bio', $user->bio ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-gray-400">Shown on your public author profile.</p>
                </div>
                @endif

                <div class="flex justify-end pt-1">
                    <button type="submit"
                            class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2 rounded-lg transition-colors">
                        Save changes
                    </button>
                </div>
            </div>
        </form>

        {{-- Change password --}}
        <form method="POST" action="{{ route('contensio.account.profile.password') }}">
            @csrf @method('PUT')
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h2 class="text-base font-bold text-gray-900">Change password</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current password</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                        <p class="mt-1 text-xs text-gray-400">Minimum 8 characters.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent">
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit"
                            class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-5 py-2 rounded-lg transition-colors">
                        Update password
                    </button>
                </div>
            </div>
        </form>

        {{-- Two-factor authentication --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">

            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-gray-900">Two-factor authentication</h2>
                        @if($twoFactor['enabled'])
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enabled
                        </span>
                        @elseif($twoFactor['pending'])
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">Pending confirmation</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5">Add a second step to your sign-in using an authenticator app.</p>
                </div>
            </div>

            @if($twoFactor['disabled'])
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Enable 2FA
                </button>
                <p class="text-xs text-gray-400 mt-2">You'll be asked to confirm your password first.</p>
            </form>
            @endif

            @if($twoFactor['pending'])
            <div class="border-t border-gray-100 pt-4 space-y-4">
                <p class="text-sm text-gray-700 leading-relaxed">
                    Scan this QR code with your authenticator app
                    (<a href="https://support.google.com/accounts/answer/1066447" target="_blank" rel="noopener" class="text-ember-600 hover:underline">Google Authenticator</a>,
                    1Password, Authy, etc.), then enter the 6-digit code below to finish.
                </p>

                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <div class="bg-white p-3 rounded-lg border border-gray-200 shrink-0">{!! $qrCode !!}</div>
                    <div class="flex-1 text-xs text-gray-500 space-y-2" x-data="{ showKey: false }">
                        <p class="text-gray-700 font-medium">Can't scan?</p>
                        <p>Type this setup key into your app manually:</p>
                        <div class="flex items-center gap-2">
                            <code x-show="showKey" x-cloak
                                  class="flex-1 bg-gray-100 border border-gray-200 px-2 py-1.5 rounded text-xs font-mono break-all text-gray-800">{{ decrypt($user->two_factor_secret) }}</code>
                            <button type="button" x-show="!showKey" @click="showKey = true" class="text-ember-600 hover:text-ember-700 font-medium">Show setup key</button>
                            <button type="button" x-show="showKey" x-cloak
                                    @click="navigator.clipboard.writeText(@js(decrypt($user->two_factor_secret))); $el.textContent='Copied ✓'; setTimeout(()=>$el.textContent='Copy',1500)"
                                    class="text-ember-600 hover:text-ember-700 font-medium whitespace-nowrap">Copy</button>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}" class="space-y-3">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700">Verification code from your app</label>
                    <div class="flex gap-2">
                        <input type="text" name="code" required inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" maxlength="6"
                               class="flex-1 sm:max-w-xs border border-gray-300 rounded-lg px-3.5 py-2.5 text-center text-lg tracking-[0.5em] font-mono focus:outline-none focus:ring-2 focus:ring-ember-500 focus:border-transparent"
                               placeholder="••••••">
                        <button type="submit" class="bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-4 rounded-lg transition-colors">Confirm</button>
                    </div>
                </form>

                <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-gray-500 hover:text-red-600 font-medium">Cancel setup</button>
                </form>
            </div>
            @endif

            @if($twoFactor['enabled'])
            <div class="border-t border-gray-100 pt-4 space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">Recovery codes</h3>
                    <p class="text-xs text-gray-500 mb-3">Store these somewhere safe. Each code can be used once to sign in if you lose access to your authenticator.</p>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 font-mono text-sm grid grid-cols-2 gap-x-6 gap-y-1">
                        @foreach($recoveryCodes as $code)<div class="text-gray-700">{{ $code }}</div>@endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ url('/user/two-factor-recovery-codes') }}">
                        @csrf
                        <button type="submit" class="text-sm text-ember-600 hover:text-ember-700 font-medium">Regenerate recovery codes</button>
                    </form>
                    <span class="text-gray-300">·</span>
                    <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">Disable 2FA</button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- Plugins can add cards here (linked accounts, API tokens, preferences, etc.) --}}
        {!! \Contensio\Support\Hook::render('contensio/admin/profile-sections', $user) !!}

        @if($canDeleteAccount)
        {{-- Delete account --}}
        <div class="bg-white rounded-xl border border-red-200 p-6 space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Delete account</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Permanently remove your account and all associated data. This action cannot be undone.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('contensio.account.profile.destroy') }}"
                  id="delete-account-form" class="border-t border-red-100 pt-4 space-y-4">
                @csrf @method('DELETE')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm your password</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                           class="w-full sm:max-w-sm rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                    @error('current_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        @click.prevent="$dispatch('cms:confirm', {
                            title: 'Delete your account?',
                            description: 'This will permanently delete your account and all your data. You cannot undo this.',
                            confirmLabel: 'Delete account',
                            formId: 'delete-account-form'
                        })"
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete my account
                </button>
            </form>
        </div>
        @endif

    </section>

</div>

<script>
function avatarUploader() {
    return {
        open:      false,
        saving:    false,
        sourceUrl: null,
        cropper:   null,

        openPicker() { this.$refs.file.click(); },

        fileChosen(event) {
            const file = event.target.files[0];
            if (! file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                this.sourceUrl = e.target.result;
                this.open      = true;
                this.$nextTick(() => this.initCropper());
            };
            reader.readAsDataURL(file);

            event.target.value = ''; // allow re-picking the same file
        },

        initCropper() {
            if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
            const img = this.$refs.cropperImg;
            this.cropper = new Cropper(img, {
                aspectRatio:   1,
                viewMode:      1,
                dragMode:      'move',
                autoCropArea:  0.9,
                cropBoxMovable:   true,
                cropBoxResizable: true,
                responsive:    true,
                background:    false,
                guides:        true,
                zoomOnWheel:   true,
            });
        },

        setZoom(val) {
            if (! this.cropper) return;
            // map -1..2 range to zoomTo ratio relative to "fit"
            this.cropper.zoomTo(parseFloat(val) + 1);
        },

        close() {
            this.open = false;
            if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
            this.sourceUrl = null;
        },

        save() {
            if (! this.cropper || this.saving) return;
            this.saving = true;

            const canvas = this.cropper.getCroppedCanvas({
                width:  512,
                height: 512,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob(async (blob) => {
                const fd = new FormData();
                fd.append('avatar', blob, 'avatar.jpg');
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                try {
                    const res = await fetch('{{ route('contensio.account.profile.avatar.update') }}', {
                        method: 'POST',
                        body:   fd,
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (! res.ok) throw new Error('Upload failed (HTTP ' + res.status + ')');
                    // Simple reload — picks up the new avatar everywhere
                    window.location.reload();
                } catch (e) {
                    alert(e.message);
                    this.saving = false;
                }
            }, 'image/jpeg', 0.85);
        },
    };
}
</script>

@endsection
