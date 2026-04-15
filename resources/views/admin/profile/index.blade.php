{{--
 | Contensio - The open content platform for Laravel.
 | Admin — profile page (current user's account + 2FA).
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

@extends('cms::admin.layout')

@section('title', 'My profile')

@section('breadcrumb')
<span class="font-medium text-gray-700">My profile</span>
@endsection

@section('content')

<div class="max-w-3xl mx-auto">

    <div class="flex items-start gap-4 mb-6">
        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xl font-bold shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="flex-1">
            <h1 class="text-xl font-bold text-gray-900">{{ $user->name }}</h1>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
        </div>
    </div>

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
        @foreach($errors->all() as $err)
        <p>{{ $err }}</p>
        @endforeach
    </div>
    @endif

    {{-- Basic info --}}
    <form method="POST" action="{{ route('cms.admin.profile.update') }}" class="mb-4">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-base font-bold text-gray-900">Account details</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors">
                    Save
                </button>
            </div>
        </div>
    </form>

    {{-- Change password --}}
    <form method="POST" action="{{ route('cms.admin.profile.password') }}" class="mb-4">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-base font-bold text-gray-900">Change password</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current password</label>
                <input type="password" name="current_password" required autocomplete="current-password"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New password</label>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-400">Minimum 8 characters.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors">
                    Update password
                </button>
            </div>
        </div>
    </form>

    {{-- Two-factor authentication --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4 space-y-4">

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
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                        Pending confirmation
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">
                    Add a second step to your sign-in using an authenticator app.
                </p>
            </div>
        </div>

        {{-- State 1: disabled → show Enable button --}}
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

        {{-- State 2: pending — show QR + confirmation input --}}
        @if($twoFactor['pending'])
        <div class="border-t border-gray-100 pt-4 space-y-4">
            <p class="text-sm text-gray-700 leading-relaxed">
                Scan this QR code with your authenticator app
                (<a href="https://support.google.com/accounts/answer/1066447" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Google Authenticator</a>,
                1Password, Authy, etc.), then enter the 6-digit code below to finish.
            </p>

            <div class="flex flex-col sm:flex-row items-start gap-4">
                <div class="bg-white p-3 rounded-lg border border-gray-200 shrink-0">
                    {!! $qrCode !!}
                </div>
                <div class="flex-1 text-xs text-gray-500 space-y-2" x-data="{ showKey: false }">
                    <p class="text-gray-700 font-medium">Can't scan?</p>
                    <p>Type this setup key into your app manually:</p>
                    <div class="flex items-center gap-2">
                        <code x-show="showKey" x-cloak
                              class="flex-1 bg-gray-100 border border-gray-200 px-2 py-1.5 rounded text-xs font-mono break-all text-gray-800">{{ decrypt($user->two_factor_secret) }}</code>
                        <button type="button" x-show="!showKey"
                                @click="showKey = true"
                                class="text-blue-600 hover:text-blue-700 font-medium">
                            Show setup key
                        </button>
                        <button type="button" x-show="showKey" x-cloak
                                @click="navigator.clipboard.writeText(@js(decrypt($user->two_factor_secret))); $el.textContent='Copied ✓'; setTimeout(()=>$el.textContent='Copy',1500)"
                                class="text-blue-600 hover:text-blue-700 font-medium whitespace-nowrap">
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            {{-- Confirmation input --}}
            <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}" class="space-y-3">
                @csrf
                <label class="block text-sm font-medium text-gray-700">Verification code from your app</label>
                <div class="flex gap-2">
                    <input type="text" name="code" required inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code"
                           maxlength="6"
                           class="flex-1 sm:max-w-xs border border-gray-300 rounded-lg px-3.5 py-2.5 text-center text-lg tracking-[0.5em] font-mono
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="••••••">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-4 rounded-lg transition-colors">
                        Confirm
                    </button>
                </div>
            </form>

            {{-- Cancel — delete the unconfirmed secret --}}
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-gray-500 hover:text-red-600 font-medium">
                    Cancel setup
                </button>
            </form>
        </div>
        @endif

        {{-- State 3: enabled — show recovery codes + disable --}}
        @if($twoFactor['enabled'])
        <div class="border-t border-gray-100 pt-4 space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-1">Recovery codes</h3>
                <p class="text-xs text-gray-500 mb-3">Store these somewhere safe. Each code can be used once to sign in if you lose access to your authenticator.</p>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 font-mono text-sm grid grid-cols-2 gap-x-6 gap-y-1">
                    @foreach($recoveryCodes as $code)
                    <div class="text-gray-700">{{ $code }}</div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3">
                <form method="POST" action="{{ url('/user/two-factor-recovery-codes') }}">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        Regenerate recovery codes
                    </button>
                </form>

                <span class="text-gray-300">·</span>

                <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">
                        Disable 2FA
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

    {{-- Plugins can add cards here (linked accounts, API tokens, preferences, etc.) --}}
    {!! \Contensio\Cms\Support\Hook::render('profile.sections', $user) !!}

</div>

@endsection
