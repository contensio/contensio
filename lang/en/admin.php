<?php

return [

    'save'   => 'Save',
    'cancel' => 'Cancel',

    'nav' => [
        'dashboard'     => 'Dashboard',
        'content'       => 'Content',
        'pages'         => 'Pages',
        'posts'         => 'Posts',
        'media'         => 'Media',
        'media_library' => 'Media Library',
        'appearance'    => 'Appearance',
        'menus'         => 'Menus',
        'users'         => 'Users',
        'admin_section' => 'Admin',
        'settings'      => 'Settings',
        'plugins'       => 'Plugins',
    ],

    'header' => [
        'view_site'  => 'View site',
        'my_profile' => 'My Profile',
        'sign_out'   => 'Sign out',
    ],

    'footer' => [
        'version' => 'Version :version',
    ],

    'pages' => [
        'title'          => 'Pages',
        'subtitle'       => 'Manage your website pages.',
        'create'         => 'New Page',
        'empty_title'    => 'No pages yet',
        'empty_subtitle' => 'Create your first page to get started.',
    ],

    'posts' => [
        'title'          => 'Posts',
        'subtitle'       => 'Write and manage your blog posts.',
        'create'         => 'New Post',
        'empty_title'    => 'No posts yet',
        'empty_subtitle' => 'Write your first post to get started.',
    ],

    'media' => [
        'title'          => 'Media Library',
        'subtitle'       => 'Upload and manage your images, documents and files.',
        'upload'         => 'Upload Files',
        'empty_title'    => 'No files yet',
        'empty_subtitle' => 'Upload your first file to get started.',
    ],

    'menus' => [
        'title'          => 'Menus',
        'subtitle'       => 'Build and manage navigation menus for your website.',
        'create'         => 'New Menu',
        'empty_title'    => 'No menus yet',
        'empty_subtitle' => 'Create your first menu to get started.',
    ],

    'users' => [
        'title'          => 'Users',
        'subtitle'       => 'Manage users and their access roles.',
        'create'         => 'New User',
        'empty_title'    => 'No users found',
        'empty_subtitle' => 'Add team members and assign roles.',
    ],

    'settings' => [
        'title'    => 'Settings',
        'subtitle' => 'Configure your website and CMS settings.',
        'save'     => 'Save Changes',
        'saved'    => 'Changes saved.',
    ],

    'plugins' => [
        'title'          => 'Plugins',
        'subtitle'       => 'Extend your website with plugins.',
        'install'        => 'Install Plugin',
        'empty_title'    => 'No plugins installed',
        'empty_subtitle' => 'Plugins extend your website with additional features.',
    ],

    'contact' => [
        // Page header
        'title'    => 'Contact',
        'subtitle' => 'Manage your contact page, form fields, and incoming messages.',
        'saved'    => 'Settings saved.',

        // Tabs
        'tab_builder'    => 'Page Builder',
        'tab_fields'     => 'Fields',
        'tab_appearance' => 'Appearance',
        'tab_settings'   => 'Settings',

        // Builder
        'view_messages'   => 'View Messages',
        'add_section'     => 'Add Section',
        'form_placeholder'=> 'Contact form will appear here',
        'configure_fields'=> 'Configure fields in the Fields tab',
        'map_address'     => 'Address or coordinates',
        'map_address_hint'=> 'e.g. "New York, NY" or "48.8584, 2.2945"',
        'map_zoom'        => 'Zoom level',

        // Fields tab
        'add_field'       => 'Add Field',
        'fields_hint'     => 'Default fields (Name, Email, Subject, Message) cannot be deleted.',
        'edit_field'      => 'Edit',
        'field_type'      => 'Field type',
        'field_key'       => 'Field key',
        'field_key_hint'  => 'Lowercase letters, numbers and underscores. e.g. "company_name"',
        'field_required'  => 'Required',
        'field_width'     => 'Width',
        'width_full'      => 'Full',
        'width_half'      => '1/2',
        'width_third'     => '1/3',
        'width_quarter'   => '1/4',
        'field_label'     => 'Label',
        'field_label_placeholder' => 'e.g. Company Name',
        'field_placeholder'       => 'Placeholder',
        'field_description'       => 'Description (shown below field)',
        'enabled'         => 'Enabled',

        // Appearance
        'field_size'      => 'Field size',
        'size_small'      => 'Small',
        'size_normal'     => 'Normal',
        'size_large'      => 'Large',
        'layout_template' => 'Layout template',
        'layout_classic'  => 'Classic',
        'layout_wide'     => 'Wide',
        'layout_split'    => 'Split',
        'layout_card'     => 'Card',
        'save_appearance' => 'Save Appearance',

        // Settings — URL
        'section_url'                => 'URL & Messages',
        'slug'                       => 'Page URL slug',
        'slug_hint'                  => 'The URL path for your contact page, e.g. /contact',
        'success_message'            => 'Success message',
        'success_message_placeholder'=> 'Thank you! We\'ll be in touch soon.',
        'redirect'                   => 'After submit',
        'redirect_same_page'         => 'Stay on same page',
        'redirect_url'               => 'Redirect to URL',

        // Settings — Antispam
        'section_antispam'        => 'Antispam',
        'antispam_honeypot'       => 'Honeypot (invisible trap field)',
        'antispam_honeypot_hint'  => 'Silently rejects bots that fill hidden fields',
        'antispam_time_check'     => 'Time check',
        'antispam_time_check_hint'=> 'Rejects submissions made too quickly after page load',
        'antispam_min_seconds'    => 'Minimum seconds before submit',
        'antispam_rate_limit'     => 'Rate limiting (per IP)',
        'antispam_math'           => 'Math question captcha',
        'recaptcha_title'         => 'Google reCAPTCHA v2',
        'recaptcha_site_key'      => 'Site key',
        'recaptcha_secret_key'    => 'Secret key',
        'turnstile_title'         => 'Cloudflare Turnstile',
        'turnstile_site_key'      => 'Site key',
        'turnstile_secret_key'    => 'Secret key',

        // Settings — Notifications
        'section_notifications'      => 'Notifications',
        'notify_admin_email'         => 'Admin notification email',
        'notify_admin_email_hint'    => 'An email is sent to this address for every new submission',
        'auto_reply'                 => 'Auto-reply to sender',
        'auto_reply_subject'         => 'Subject',
        'auto_reply_subject_placeholder' => 'We received your message',
        'auto_reply_body'            => 'Body',
        'webhook'                    => 'Webhook',
        'webhook_hint'               => 'POST a JSON payload to this URL for every submission',

        // Settings — File uploads
        'section_file_uploads' => 'File Uploads',
        'max_files'            => 'Max files',
        'max_size_mb'          => 'Max file size (MB)',
        'allowed_types'        => 'Allowed types',
        'allowed_types_hint'   => 'Comma-separated extensions: jpg,png,pdf,doc',

        // Settings — GDPR
        'section_gdpr'      => 'GDPR Consent',
        'gdpr_required'     => 'Require consent before submit',
        'gdpr_text'         => 'Consent text',
        'gdpr_text_placeholder' => 'I agree to the processing of my personal data.',

        // Builder block delete
        'delete_block_title'   => 'Delete block',
        'delete_block_desc'    => 'This block and all its content will be removed.',
        'delete_block_confirm' => 'Delete',
        'delete_item_title'    => 'Delete item',
        'delete_item_desc'     => 'This item will be removed from the block.',

        // Accordion section
        'accordion_add_item'            => 'Add item',
        'accordion_title_placeholder'   => 'Question / title…',
        'accordion_content_placeholder' => 'Answer / content…',

        'save_builder'  => 'Save Builder',
        'save_settings' => 'Save Settings',

        // Messages inbox
        'messages' => [
            'title'        => 'Messages',
            'subtitle'     => 'Incoming contact form submissions.',
            'detail_title' => 'Message',

            'tab_all'     => 'All',
            'tab_new'     => 'New',
            'tab_read'    => 'Read',
            'tab_replied' => 'Replied',
            'tab_spam'    => 'Spam',

            'export_csv'   => 'Export CSV',
            'settings_btn' => 'Contact page settings',

            'search_placeholder' => 'Search by name, email, subject…',
            'search_btn'         => 'Search',
            'clear'              => 'Clear',

            'empty_title'    => 'No messages yet',
            'empty_subtitle' => 'Contact form submissions will appear here.',
            'empty_search'   => 'No messages match your search.',

            'select_all'   => 'Select all',
            'select_first' => 'Select at least one message first.',

            'mark_read'    => 'Mark Read',
            'mark_replied' => 'Mark Replied',
            'mark_spam'    => 'Mark Spam',
            'not_spam'     => 'Not Spam',
            'delete'       => 'Delete',
            'back'         => 'Back to inbox',

            'confirm_mark_read'        => 'Mark selected as read?',
            'confirm_mark_replied'     => 'Mark selected as replied?',
            'confirm_mark_spam'        => 'Mark selected as spam?',
            'confirm_delete_bulk'      => 'Delete selected messages?',
            'confirm_delete_bulk_desc' => 'This will permanently delete the selected messages and their attachments.',
            'confirm_delete_one'       => 'Delete this message?',
            'confirm_delete_one_desc'  => 'This will permanently delete the message and its attachments.',

            'extra_fields'    => 'Extra fields',
            'attachments'     => 'Attachments',
            'no_subject'      => '(no subject)',
            'read_at'         => 'Read',
            'reply_via_email' => 'Reply via email',

            // Labels
            'manage_labels'  => 'Labels',
            'filter_label'   => 'Label:',
            'filter_all'     => 'All',
            'assign_label'   => 'Assign label',
            'add_label'      => 'Add label',
        ],

        'labels' => [
            'title'    => 'Labels',
            'subtitle' => 'Create and manage labels to organise your messages.',
            'create'   => 'New Label',
            'new_label'=> 'New label',
            'save'     => 'Save',
            'edit'     => 'Edit',
            'delete'   => 'Delete',
            'empty'    => 'No labels yet.',
            'empty_hint'=> 'Click "New Label" to create your first one.',

            'field_name'      => 'Name',
            'field_color'     => 'Colour',
            'name_placeholder'=> 'e.g. Sales lead',

            'message_singular' => 'message',
            'message_plural'   => 'messages',

            'delete_title'   => 'Delete label',
            'delete_desc'    => 'The label will be removed from all messages.',
            'delete_confirm' => 'Delete',

            'created' => 'Label created.',
            'updated' => 'Label updated.',
            'deleted' => 'Label deleted.',
        ],
    ],

    'backup' => [
        'created'                => 'Backup created successfully.',
        'deleted'                => 'Backup deleted.',
        'restored'               => 'Restore complete — :tables tables and :files files restored.',
        'wrong_password'         => 'Incorrect password. Please try again.',
        'restore_session_expired' => 'Restore session expired. Please upload the backup file again.',
    ],

    'update' => [
        'title'         => 'Contensio :version is available',
        'subtitle'      => 'A new version of Contensio is ready. Run these two commands on your server to upgrade:',
        'release_notes' => 'Release notes',
        'upgrade_guide' => 'Upgrade guide',
        'available'     => 'available',
    ],

    'dashboard' => [
        'title'   => 'Dashboard',
        'welcome' => 'Welcome back, :name.',

        'stats' => [
            'content'            => 'Content',
            'content_subtitle'   => 'Total items',
            'media'              => 'Media',
            'media_subtitle'     => 'Files uploaded',
            'comments'           => 'Comments',
            'comments_subtitle'  => 'Pending review',
            'users'              => 'Users',
            'users_subtitle'     => 'Registered',
        ],

        'recent_content'   => 'Recent Content',
        'view_all'         => 'View all',
        'no_content'       => 'No content yet',
        'recent_activity'  => 'Recent Activity',
        'no_activity'      => 'No activity yet',
        'untitled'         => '(untitled)',
    ],

];
