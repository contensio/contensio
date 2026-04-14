{{--
 | Contensio - The open content platform for Laravel.
 | Frontend — layout.
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
    <title>@yield('title', $site['name'])</title>
    <meta name="description" content="@yield('meta_description', $site['tagline'] ?? '')">
    @hasSection('meta_title')
    <meta property="og:title" content="@yield('meta_title')">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .prose { max-width: none; }
        .prose h2 { font-size: 1.5rem; font-weight: 700; margin: 2rem 0 0.75rem; color: #111827; }
        .prose h3 { font-size: 1.25rem; font-weight: 700; margin: 1.75rem 0 0.625rem; color: #111827; }
        .prose h4 { font-size: 1.1rem; font-weight: 600; margin: 1.5rem 0 0.5rem; color: #111827; }
        .prose p  { margin: 1rem 0; line-height: 1.75; color: #374151; }
        .prose ul { list-style: disc; padding-left: 1.5rem; margin: 1rem 0; color: #374151; }
        .prose ol { list-style: decimal; padding-left: 1.5rem; margin: 1rem 0; color: #374151; }
        .prose li { margin: 0.375rem 0; line-height: 1.75; }
        .prose a  { color: #2563eb; text-decoration: underline; }
        .prose a:hover { color: #1d4ed8; }
        .prose strong { font-weight: 600; color: #111827; }
        .prose blockquote { border-left: 4px solid #e5e7eb; padding-left: 1rem; margin: 1.5rem 0; color: #6b7280; font-style: italic; }
        .prose pre { background: #1e293b; color: #e2e8f0; padding: 1.25rem; border-radius: 0.5rem; overflow-x: auto; margin: 1.5rem 0; font-size: 0.875rem; }
        .prose code { font-family: monospace; font-size: 0.875em; }
        .prose img { max-width: 100%; border-radius: 0.5rem; margin: 1.5rem 0; }
        .prose hr { border: 0; border-top: 1px solid #e5e7eb; margin: 2rem 0; }
    </style>
    @stack('head')
</head>
<body class="bg-white text-gray-900 antialiased">

    {{-- Header --}}
    <header class="border-b border-gray-100">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            <a href="{{ route('cms.home') }}"
               class="font-bold text-gray-900 text-lg hover:text-blue-600 transition-colors">
                {{ $site['name'] }}
            </a>
            <nav class="flex items-center gap-6 text-sm text-gray-500">
                <a href="{{ route('cms.home') }}" class="hover:text-gray-900 transition-colors">Home</a>
                <a href="{{ route('cms.blog') }}" class="hover:text-gray-900 transition-colors">Blog</a>
            </nav>
        </div>
    </header>

    {{-- Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-100 mt-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-sm text-gray-400">
                &copy; {{ date('Y') }} {{ $site['name'] }}
            </p>
            @if($site['tagline'])
            <p class="text-sm text-gray-400">{{ $site['tagline'] }}</p>
            @endif
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
