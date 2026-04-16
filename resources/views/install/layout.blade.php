{{--
 | Contensio - The open content platform for Laravel.
 | Install — layout.
 | https://contensio.com
 |
 | Copyright (c) 2026 Iosif Gabriel Chimilevschi
 | @license  AGPL-3.0-or-later  https://www.gnu.org/licenses/agpl-3.0.txt
 | @author   Iosif Gabriel Chimilevschi <office@contensio.com>
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('contensio::install.title') }}</title>
    <link rel="icon" type="image/png" href="{{ asset(config('contensio.admin_favicon', 'vendor/contensio/img/favicon128x128.png')) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'ember-500': '#d04a1f',
                        'ember-600': '#b23e18',
                        'ember-700': '#8f3112',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen" style="background-color: #fbf8f0;">

    {{-- Header --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-2xl mx-auto px-4 py-5 flex items-center justify-center">
            <img src="{{ asset(config('contensio.admin_logo', 'vendor/contensio/img/logo.png')) }}"
                 alt="{{ config('contensio.name', 'Contensio') }}">
        </div>
    </div>

    {{-- Steps indicator (hidden on requirements page) --}}
    @isset($currentStep)
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-2xl mx-auto px-4 py-4">
            <div class="flex items-center gap-2">
                @foreach([
                    1 => __('contensio::install.steps.database'),
                    2 => __('contensio::install.steps.website'),
                    3 => __('contensio::install.steps.account'),
                    4 => __('contensio::install.steps.done'),
                ] as $num => $label)
                    <div class="flex items-center gap-2 {{ $num < 4 ? 'flex-1' : '' }}">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-medium
                                {{ $currentStep > $num ? 'bg-green-500 text-white' : ($currentStep == $num ? 'bg-ember-500 text-white' : 'bg-gray-200 text-gray-500') }}">
                                @if($currentStep > $num)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    {{ $num }}
                                @endif
                            </div>
                            <span class="text-sm {{ $currentStep == $num ? 'text-gray-900 font-medium' : 'text-gray-400' }} hidden sm:inline">
                                {{ $label }}
                            </span>
                        </div>
                        @if($num < 4)
                            <div class="flex-1 h-px {{ $currentStep > $num ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endisset

    {{-- Content --}}
    <div class="max-w-2xl mx-auto px-4 py-10">
        @yield('content')
    </div>

</body>
</html>
