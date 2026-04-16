{{--
 | Contensio - The open content platform for Laravel.
 | Admin — Email / SMTP settings.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
--}}

@extends('cms::admin.layout')

@section('title', 'Email Settings')

@section('breadcrumb')
    <a href="{{ route('cms.admin.settings.index') }}" class="text-gray-500 hover:text-gray-700">Configuration</a>
    <span class="mx-2 text-gray-300">/</span>
    <span class="text-gray-900 font-medium">Email</span>
@endsection

@section('content')

<div class="max-w-3xl mx-auto">

    <h1 class="text-xl font-bold text-gray-900 mb-1">Email settings</h1>
    <p class="text-sm text-gray-500 mb-5">Used for password reset, email verification, and notifications.</p>

    @if (session('success'))
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <i class="bi bi-check-circle-fill text-green-600"></i>
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        <i class="bi bi-exclamation-triangle-fill text-red-600 shrink-0 mt-0.5"></i>
        <span class="break-all">{{ session('error') }}</span>
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
        <ul class="text-sm text-red-700 space-y-0.5">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('cms.admin.settings.email.save') }}"
          class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @csrf

        <div class="p-5 space-y-5">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mailer</label>
                <select name="mailer"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach(['smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'log' => 'Log (dev)', 'array' => 'Array (testing)'] as $k => $label)
                    <option value="{{ $k }}" {{ old('mailer', $settings['mailer']) === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">SMTP is the usual choice. "Log" writes emails to your Laravel log (useful in development).</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">SMTP host</label>
                    <input type="text" name="host" value="{{ old('host', $settings['host']) }}"
                           placeholder="smtp.example.com"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                    <input type="number" name="port" value="{{ old('port', $settings['port']) }}"
                           placeholder="587"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
                <select name="encryption"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach(['tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)', '' => 'None'] as $k => $label)
                    <option value="{{ $k }}" {{ old('encryption', $settings['encryption']) === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" value="{{ old('username', $settings['username']) }}"
                           autocomplete="off"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password"
                           autocomplete="new-password"
                           placeholder="{{ ! empty($settings['password']) ? '••••••••••••••••' : '' }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500">Leave blank to keep the current password.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Sender</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From address</label>
                        <input type="email" name="from_address" value="{{ old('from_address', $settings['from_address']) }}"
                               placeholder="no-reply@example.com"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From name</label>
                        <input type="text" name="from_name" value="{{ old('from_name', $settings['from_name']) }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

        </div>

        <div class="flex items-center justify-end gap-3 px-5 py-3 bg-gray-50 border-t border-gray-100">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 rounded-lg transition-colors">
                Save settings
            </button>
        </div>
    </form>

    {{-- Test email --}}
    <form method="POST" action="{{ route('cms.admin.settings.email.test') }}"
          class="mt-5 bg-white rounded-xl border border-gray-200 p-5">
        @csrf
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                <i class="bi bi-send text-emerald-600"></i>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-900">Send a test email</h2>
                <p class="text-xs text-gray-500">Verify that outbound mail works with the settings above.</p>
            </div>
        </div>
        <div class="flex items-stretch gap-2">
            <input type="email" name="to" required
                   value="{{ auth()->user()->email }}"
                   placeholder="your@email.com"
                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm
                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            <button type="submit"
                    class="shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors">
                Send test
            </button>
        </div>
    </form>

</div>

@endsection
