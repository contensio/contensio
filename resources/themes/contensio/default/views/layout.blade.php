@php
    // Theme customization values
    $primary      = theme_option('primary_color', '#2563eb');
    $primaryHover = theme_option('primary_hover', '#1d4ed8');
    $textColor    = theme_option('text_color', '#111827');
    $mutedColor   = theme_option('muted_color', '#6b7280');
    $bgColor      = theme_option('bg_color', '#ffffff');
    $baseSize     = (int) theme_option('base_font_size', 16);
    $containerW   = (int) theme_option('container_width', 768);
    $headerStyle  = theme_option('header_style', 'minimal');
    $headingFont  = theme_option('heading_font', 'inter');
    $bodyFont     = theme_option('body_font', 'inter');
    $customCss    = (string) theme_option('custom_css', '');
    $headSnippet  = (string) theme_option('head_snippet', '');
    $logoText     = trim((string) theme_option('logo_text', '')) ?: $site['name'];
    $footerTpl    = (string) theme_option('footer_text', '© {year} {site_name}');
    $showTagline  = (bool) theme_option('show_footer_tagline', true);

    // Font family stacks
    $fontMap = [
        'inter'        => ['family' => 'Inter',              'google' => 'Inter:wght@400;500;600;700;800', 'stack' => "'Inter', system-ui, sans-serif"],
        'playfair'     => ['family' => 'Playfair Display',   'google' => 'Playfair+Display:wght@400;600;700;800', 'stack' => "'Playfair Display', Georgia, serif"],
        'merriweather' => ['family' => 'Merriweather',       'google' => 'Merriweather:wght@400;700;900', 'stack' => "'Merriweather', Georgia, serif"],
        'poppins'      => ['family' => 'Poppins',            'google' => 'Poppins:wght@400;500;600;700;800', 'stack' => "'Poppins', system-ui, sans-serif"],
        'jetbrains'    => ['family' => 'JetBrains Mono',     'google' => 'JetBrains+Mono:wght@400;600;700', 'stack' => "'JetBrains Mono', ui-monospace, monospace"],
        'system'       => ['family' => null,                 'google' => null, 'stack' => "system-ui, -apple-system, Segoe UI, Roboto, sans-serif"],
        'georgia'      => ['family' => null,                 'google' => null, 'stack' => "Georgia, 'Times New Roman', serif"],
    ];

    $headingFontStack = $fontMap[$headingFont]['stack'] ?? $fontMap['inter']['stack'];
    $bodyFontStack    = $fontMap[$bodyFont]['stack']    ?? $fontMap['inter']['stack'];

    $googleFonts = array_values(array_unique(array_filter([
        $fontMap[$headingFont]['google'] ?? null,
        $fontMap[$bodyFont]['google']    ?? null,
    ])));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        // SEO helpers — expose site-wide defaults + per-content overrides.
        // Views opt-in via @section('meta_robots', 'noindex') or @section('og_image', '...')
        $siteNoindex  = (bool) \Contensio\Models\Setting::where('module', 'core')->where('setting_key', 'seo_noindex')->value('value');
        $siteOgImage  = \Contensio\Models\Setting::where('module', 'core')->where('setting_key', 'default_og_image')->value('value');
        $verification = \Contensio\Models\Setting::where('module', 'core')->where('setting_key', 'google_site_verification')->value('value');

        $pageRobots = trim($__env->yieldContent('meta_robots'));
        $pageOgImg  = trim($__env->yieldContent('og_image'));
        $ogImage    = $pageOgImg ?: $siteOgImage;
        $isNoindex  = $siteNoindex || $pageRobots === 'noindex';
    @endphp

    <title>@yield('title', $site['name'])</title>
    <meta name="description" content="@yield('meta_description', $site['tagline'] ?? '')">

    {{-- Robots meta: site-wide noindex OR per-page --}}
    @if($isNoindex)
    <meta name="robots" content="noindex, nofollow">
    @else
    <meta name="robots" content="index, follow">
    @endif

    {{-- Google Search Console verification --}}
    @if($verification)
    <meta name="google-site-verification" content="{{ $verification }}">
    @endif

    {{-- Open Graph --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $site['name'] }}">
    <meta property="og:title" content="@yield('meta_title', $site['name'])">
    <meta property="og:description" content="@yield('meta_description', $site['tagline'] ?? '')">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $ogImage }}">
    @else
    <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="@yield('meta_title', $site['name'])">
    <meta name="twitter:description" content="@yield('meta_description', $site['tagline'] ?? '')">

    <link rel="icon" href="{{ site_favicon() ?: asset('vendor/contensio/img/favicon128x128.png') }}">

    @if(! empty($googleFonts))
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?{{ implode('&', array_map(fn($f) => 'family=' . $f, $googleFonts)) }}&display=swap" rel="stylesheet">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --theme-primary: {{ $primary }};
            --theme-primary-hover: {{ $primaryHover }};
            --theme-text: {{ $textColor }};
            --theme-muted: {{ $mutedColor }};
            --theme-bg: {{ $bgColor }};
            --theme-container: {{ $containerW }}px;
            --theme-heading-font: {{ $headingFontStack }};
            --theme-body-font: {{ $bodyFontStack }};
        }
        html { font-size: {{ $baseSize }}px; }
        body { background: var(--theme-bg); color: var(--theme-text); font-family: var(--theme-body-font); }
        h1, h2, h3, h4, h5, h6 { font-family: var(--theme-heading-font); color: var(--theme-text); }
        a { color: var(--theme-primary); }
        a:hover { color: var(--theme-primary-hover); }
        .theme-container { max-width: var(--theme-container); margin-left: auto; margin-right: auto; }
        .theme-btn-primary { background: var(--theme-primary); color: #fff; }
        .theme-btn-primary:hover { background: var(--theme-primary-hover); }

        /* Menus */
        .theme-nav ul { list-style: none; margin: 0; padding: 0; }
        .theme-nav .menu-item { position: relative; }
        .theme-nav .menu-link {
            display: inline-flex; align-items: center; gap: 0.375rem;
            color: inherit; text-decoration: none;
            transition: opacity 0.15s ease, color 0.15s ease;
        }
        .theme-nav .menu-link:hover { opacity: 0.7; }
        .theme-nav .menu-link.is-active { color: var(--theme-primary); font-weight: 600; }
        .theme-nav .has-children > .menu-link::after {
            content: "▾"; font-size: 0.625em; margin-left: 0.125rem; opacity: 0.6;
        }
        .theme-nav .submenu {
            position: absolute; top: 100%; left: 0; min-width: 11rem;
            background: #fff; border: 1px solid #e5e7eb; border-radius: 0.5rem;
            padding: 0.5rem; box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            display: none; flex-direction: column; gap: 0.25rem; z-index: 30;
        }
        .theme-nav .has-children:hover > .submenu,
        .theme-nav .has-children:focus-within > .submenu { display: flex; }
        .theme-nav .submenu .menu-link { padding: 0.375rem 0.625rem; border-radius: 0.375rem; }
        .theme-nav .submenu .menu-link:hover { background: #f3f4f6; opacity: 1; }

        /* Prose styles */
        .prose { max-width: none; }
        .prose h2 { font-size: 1.5rem; font-weight: 700; margin: 2rem 0 0.75rem; }
        .prose h3 { font-size: 1.25rem; font-weight: 700; margin: 1.75rem 0 0.625rem; }
        .prose h4 { font-size: 1.1rem; font-weight: 600; margin: 1.5rem 0 0.5rem; }
        .prose p  { margin: 1rem 0; line-height: 1.75; color: var(--theme-text); }
        .prose ul { list-style: disc; padding-left: 1.5rem; margin: 1rem 0; color: var(--theme-text); }
        .prose ol { list-style: decimal; padding-left: 1.5rem; margin: 1rem 0; color: var(--theme-text); }
        .prose li { margin: 0.375rem 0; line-height: 1.75; }
        .prose strong { font-weight: 600; }
        .prose blockquote { border-left: 4px solid #e5e7eb; padding-left: 1rem; margin: 1.5rem 0; color: var(--theme-muted); font-style: italic; }
        .prose pre { background: #1e293b; color: #e2e8f0; padding: 1.25rem; border-radius: 0.5rem; overflow-x: auto; margin: 1.5rem 0; font-size: 0.875rem; }
        .prose code { font-family: ui-monospace, 'JetBrains Mono', monospace; font-size: 0.875em; }
        .prose img { max-width: 100%; border-radius: 0.5rem; margin: 1.5rem 0; }
        .prose hr { border: 0; border-top: 1px solid #e5e7eb; margin: 2rem 0; }
    </style>

    @if($customCss)
    <style id="theme-custom-css">
        {!! $customCss !!}
    </style>
    @endif

    @stack('head')
    {!! $headSnippet !!}
</head>
<body class="antialiased">

    @php
        $headerMenu = theme_menu('header', $lang?->id ?? null);
        $footerMenu = theme_menu('footer', $lang?->id ?? null);
    @endphp

    {{-- Header --}}
    <header class="border-b border-gray-100 {{ $headerStyle === 'sticky' ? 'sticky top-0 bg-white/90 backdrop-blur z-20' : '' }}">
        @if($headerStyle === 'centered')
        <div class="theme-container px-4 sm:px-6 py-5 text-center">
            <a href="{{ route('contensio.home') }}" class="inline-block hover:opacity-80 transition-opacity">
                @if(site_logo())
                <img src="{{ site_logo() }}" alt="{{ $logoText }}" class="h-9 w-auto mx-auto">
                @else
                <span class="font-bold text-2xl">{{ $logoText }}</span>
                @endif
            </a>
            <nav class="mt-3 theme-nav" style="color: var(--theme-muted);">
                @include('theme::partials.menu', [
                    'items' => $headerMenu,
                    'class' => 'flex items-center justify-center gap-6 text-sm list-none m-0 p-0',
                ])
            </nav>
        </div>
        @else
        <div class="theme-container px-4 sm:px-6 h-14 flex items-center justify-between gap-6">
            <a href="{{ route('contensio.home') }}" class="hover:opacity-80 transition-opacity shrink-0">
                @if(site_logo())
                <img src="{{ site_logo() }}" alt="{{ $logoText }}" class="h-8 w-auto max-w-[200px] object-contain">
                @else
                <span class="font-bold text-lg">{{ $logoText }}</span>
                @endif
            </a>
            <nav class="theme-nav" style="color: var(--theme-muted);">
                @include('theme::partials.menu', [
                    'items' => $headerMenu,
                    'class' => 'flex items-center gap-6 text-sm list-none m-0 p-0',
                ])
            </nav>
        </div>
        @endif
    </header>

    {{-- Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-100 mt-16">
        <div class="theme-container px-4 sm:px-6 py-8 space-y-4">
            @if(! empty($footerMenu))
            <nav class="theme-nav flex justify-center" style="color: var(--theme-muted);">
                @include('theme::partials.menu', [
                    'items' => $footerMenu,
                    'class' => 'flex flex-wrap items-center justify-center gap-5 text-sm list-none m-0 p-0',
                ])
            </nav>
            @endif

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm" style="color: var(--theme-muted);">
                    {{ str_replace(['{year}', '{site_name}'], [date('Y'), $site['name']], $footerTpl) }}
                </p>
                @if($showTagline && ! empty($site['tagline']))
                <p class="text-sm" style="color: var(--theme-muted);">{{ $site['tagline'] }}</p>
                @endif
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
