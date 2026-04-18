<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CMS Name
    |--------------------------------------------------------------------------
    */
    'name' => env('CONTENSIO_NAME', 'Contensio'),

    /*
    |--------------------------------------------------------------------------
    | CMS Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URI prefix for all CMS admin panel routes.
    |
    */
    'route_prefix' => env('CONTENSIO_ROUTE_PREFIX', 'account'),

    /*
    |--------------------------------------------------------------------------
    | CMS Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to all CMS admin routes.
    |
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Packages Directory
    |--------------------------------------------------------------------------
    |
    | The directory where admin-installed plugins and themes are stored.
    |
    */
    'packages_path' => base_path('packages'),

    /*
    |--------------------------------------------------------------------------
    | Admin Branding
    |--------------------------------------------------------------------------
    |
    | Paths (resolved via asset() — relative to the project's public/ directory)
    | for the admin panel logo and favicon. Defaults come from the core package
    | after you run `php artisan vendor:publish --tag=cms-assets`.
    |
    | Override to white-label a deployment — point at your own img/ files:
    |     'admin_logo'    => 'img/my-company-logo.svg',
    |     'admin_favicon' => 'img/my-company-favicon.png',
    |
    */
    'admin_logo'    => env('CONTENSIO_ADMIN_LOGO',    'vendor/contensio/img/logo.png'),
    'admin_favicon' => env('CONTENSIO_ADMIN_FAVICON', 'vendor/contensio/img/favicon128x128.png'),

    // Logo variant for the dark admin sidebar. Separate file because the
    // default admin_logo has a dark wordmark that disappears on the slate-900
    // sidebar background. Override via CONTENSIO_ADMIN_LOGO_DARK to white-label.
    'admin_logo_dark' => env('CONTENSIO_ADMIN_LOGO_DARK', 'vendor/contensio/img/logo-backend.png'),

    /*
    |--------------------------------------------------------------------------
    | Version Checking
    |--------------------------------------------------------------------------
    |
    | When enabled, the admin dashboard checks GitHub for a newer release and
    | shows a notice if one is available. Results are cached for 12 hours so
    | the GitHub API is never called on every page load.
    |
    | Set to false to disable entirely (e.g. air-gapped or offline servers).
    |
    */
    'version_check' => env('CONTENSIO_VERSION_CHECK', true),

    /*
    | GitHub repository used for release checks. Override this if you fork
    | Contensio and publish your own releases.
    */
    'github_repo' => env('CONTENSIO_GITHUB_REPO', 'contensio/contensio'),

    /*
    |--------------------------------------------------------------------------
    | Image Size Presets
    |--------------------------------------------------------------------------
    |
    | Global definitions for all image variants the CMS can generate.
    | Per-content-type activation and dimension overrides are stored in
    | content_type_meta (meta_key = "image_sizes") as JSON.
    |
    | fit options: cover, contain, scale, crop, pad
    | background   only used when fit = "pad"
    | default_for  content type names that have this size active by default;
    |              use ["*"] to activate for every content type
    |
    */
    'image_sizes' => [

        'thumbnail' => [
            'label'       => 'Thumbnail',
            'width'       => 150,
            'height'      => 150,
            'fit'         => 'pad',
            'quality'     => 85,
            'background'  => '#ffffff',
            'default_for' => [],
        ],

        'small' => [
            'label'       => 'Small',
            'width'       => 480,
            'height'      => 320,
            'fit'         => 'cover',
            'quality'     => 85,
            'background'  => '#ffffff',
            'default_for' => ['*'],
        ],

        'medium' => [
            'label'       => 'Medium',
            'width'       => 900,
            'height'      => 600,
            'fit'         => 'cover',
            'quality'     => 85,
            'background'  => '#ffffff',
            'default_for' => ['*'],
        ],

        'large' => [
            'label'       => 'Large',
            'width'       => 1600,
            'height'      => 900,
            'fit'         => 'cover',
            'quality'     => 85,
            'background'  => '#ffffff',
            'default_for' => ['*'],
        ],

        'og' => [
            'label'       => 'OG Image',
            'width'       => 1200,
            'height'      => 630,
            'fit'         => 'cover',
            'quality'     => 85,
            'background'  => '#ffffff',
            'default_for' => ['post', 'page'],
        ],

        'square' => [
            'label'       => 'Square',
            'width'       => 600,
            'height'      => 600,
            'fit'         => 'pad',
            'quality'     => 85,
            'background'  => '#ffffff',
            'default_for' => [],
        ],

    ],

];
