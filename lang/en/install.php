<?php

return [

    'title'        => 'Install Contensio',
    'installation' => 'Installation',

    'steps' => [
        'database' => 'Database',
        'website'  => 'Website',
        'account'  => 'Account',
        'done'     => 'Done',
    ],

    'requirements' => [
        'title'            => 'System Requirements',
        'subtitle'         => 'Checking if your server is ready to run Contensio.',
        'failed_title'     => 'Some requirements are not met.',
        'failed_subtitle'  => 'Contact your hosting provider to fix the issues below, then refresh this page.',
        'php_version'      => 'PHP Version',
        'php_required'     => 'PHP :version or higher',
        'extensions'       => 'PHP Extensions',
        'recommended'      => 'Recommended',
        'permissions'      => 'File Permissions',
        'disk_space'       => 'Disk Space',
        'disk_required'    => 'At least 100 MB free',
        'disk_available'   => ':mb MB available',
        'check_again'      => '↺ Check again',
        'fix_issues'       => 'Fix issues to continue',
    ],

    'database' => [
        'title'                => 'Database Connection',
        'subtitle'             => 'These details are provided by your hosting provider. Check your hosting panel (cPanel, Plesk) or the welcome email from your hosting company.',
        'host'                 => 'Database Host',
        'name'                 => 'Database Name',
        'username'             => 'Username',
        'password'             => 'Password',
        'password_placeholder' => 'Leave empty if no password',
        'advanced'             => 'Advanced options',
        'port'                 => 'Port',
        'test'                 => 'Test Connection',
        'connecting'           => 'Connecting...',
    ],

    'website' => [
        'title'              => 'Your Website',
        'subtitle'           => 'Basic information about your website. You can change all of this later from the admin panel.',
        'site_name'          => 'Website Name',
        'site_name_placeholder' => 'My Website',
        'language'           => 'Default Language',
        'language_hint'      => 'The primary language for your website content. More languages can be added later.',
    ],

    'account' => [
        'title'                        => 'Your Admin Account',
        'subtitle'                     => 'This is your login to manage the website. Keep your password safe.',
        'name'                         => 'Your Name',
        'name_placeholder'             => 'John Smith',
        'email'                        => 'Email Address',
        'email_placeholder'            => 'you@example.com',
        'email_hint'                   => 'This will be your login email address.',
        'password'                     => 'Password',
        'password_placeholder'         => 'At least 8 characters',
        'confirm_password'             => 'Confirm Password',
        'confirm_password_placeholder' => 'Repeat your password',
        'strength'                     => [
            'weak'   => 'Weak',
            'fair'   => 'Fair',
            'good'   => 'Good',
            'strong' => 'Strong',
        ],
        'submit'     => 'Install Contensio',
        'installing' => 'Installing...',
    ],

    'complete' => [
        'title'       => 'Installation Complete!',
        'subtitle'    => 'Your website is ready. Here are your details — save them somewhere safe.',
        'website_url' => 'Website URL',
        'admin_panel' => 'Admin Panel',
        'go_to_admin' => 'Go to Admin Panel',
        'note'        => 'The installer is now disabled. To reinstall, remove :key from your .env file.',
    ],

    'buttons' => [
        'continue' => 'Continue →',
        'back'     => 'Back',
    ],

];
