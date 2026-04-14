<?php

/*
|--------------------------------------------------------------------------
| CMS Core Block Types
|--------------------------------------------------------------------------
|
| Defines every block type that ships with the core CMS.
| Plugins can register additional block types via the BlockTypeRegistry.
|
| Field types: text, textarea, richtext, url, number, select, boolean, code
|
| 'translatable' => true   — value differs per language
| 'required'     => true   — validated on save
| 'options'      => [...]  — for select fields (value => label OR flat values)
| 'default'      => ...    — pre-filled value
| 'width'        => half   — layout hint: 'half' renders two per row
| 'help'         => ...    — help text shown below the field
| 'show_if'      => [field => value]  — conditional display
|
*/

return [

    // ── Text ─────────────────────────────────────────────────────────────────

    'richtext' => [
        'label'       => 'Rich Text',
        'description' => 'Formatted text with headings, lists, links, and inline styles.',
        'icon'        => 'document-text',
        'category'    => 'text',
        'sort_order'  => 1,
        'fields' => [
            'content' => [
                'type'         => 'richtext',
                'label'        => 'Content',
                'translatable' => true,
                'required'     => true,
            ],
        ],
    ],

    'heading' => [
        'label'       => 'Heading',
        'description' => 'A section heading (H2–H4).',
        'icon'        => 'hashtag',
        'category'    => 'text',
        'sort_order'  => 2,
        'fields' => [
            'text' => [
                'type'         => 'text',
                'label'        => 'Heading Text',
                'translatable' => true,
                'required'     => true,
            ],
            'level' => [
                'type'         => 'select',
                'label'        => 'Level',
                'translatable' => false,
                'default'      => 'h2',
                'options'      => ['h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4'],
                'width'        => 'half',
            ],
            'alignment' => [
                'type'         => 'select',
                'label'        => 'Alignment',
                'translatable' => false,
                'default'      => 'left',
                'options'      => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'],
                'width'        => 'half',
            ],
        ],
    ],

    'quote' => [
        'label'       => 'Quote',
        'description' => 'A styled blockquote with optional attribution.',
        'icon'        => 'chat-bubble-left',
        'category'    => 'text',
        'sort_order'  => 3,
        'fields' => [
            'quote' => [
                'type'         => 'textarea',
                'label'        => 'Quote',
                'translatable' => true,
                'required'     => true,
            ],
            'author' => [
                'type'         => 'text',
                'label'        => 'Author',
                'translatable' => true,
                'required'     => false,
                'width'        => 'half',
            ],
            'author_title' => [
                'type'         => 'text',
                'label'        => 'Author Title',
                'translatable' => true,
                'required'     => false,
                'width'        => 'half',
            ],
        ],
    ],

    'code' => [
        'label'       => 'Code',
        'description' => 'A code block with optional language label.',
        'icon'        => 'code-bracket',
        'category'    => 'text',
        'sort_order'  => 4,
        'fields' => [
            'code' => [
                'type'         => 'code',
                'label'        => 'Code',
                'translatable' => false,
                'required'     => true,
            ],
            'language' => [
                'type'         => 'select',
                'label'        => 'Language',
                'translatable' => false,
                'default'      => 'plaintext',
                'options'      => ['plaintext' => 'Plain Text', 'php' => 'PHP', 'javascript' => 'JavaScript', 'html' => 'HTML', 'css' => 'CSS', 'bash' => 'Bash', 'sql' => 'SQL', 'json' => 'JSON'],
                'width'        => 'half',
            ],
        ],
    ],

    // ── Media ─────────────────────────────────────────────────────────────────

    'image' => [
        'label'       => 'Image',
        'description' => 'A single image with optional caption.',
        'icon'        => 'photo',
        'category'    => 'media',
        'sort_order'  => 10,
        'fields' => [
            'url' => [
                'type'         => 'url',
                'label'        => 'Image URL',
                'translatable' => false,
                'required'     => true,
            ],
            'alt' => [
                'type'         => 'text',
                'label'        => 'Alt Text',
                'translatable' => true,
                'required'     => false,
            ],
            'caption' => [
                'type'         => 'text',
                'label'        => 'Caption',
                'translatable' => true,
                'required'     => false,
            ],
            'width' => [
                'type'         => 'select',
                'label'        => 'Width',
                'translatable' => false,
                'default'      => 'full',
                'options'      => ['full' => 'Full', 'wide' => 'Wide', 'normal' => 'Normal', 'small' => 'Small'],
                'width'        => 'half',
            ],
            'alignment' => [
                'type'         => 'select',
                'label'        => 'Alignment',
                'translatable' => false,
                'default'      => 'center',
                'options'      => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'],
                'width'        => 'half',
            ],
            'link_url' => [
                'type'         => 'url',
                'label'        => 'Link (optional)',
                'translatable' => false,
                'required'     => false,
            ],
        ],
    ],

    'video' => [
        'label'       => 'Video',
        'description' => 'Embed a YouTube, Vimeo, or direct video.',
        'icon'        => 'play-circle',
        'category'    => 'media',
        'sort_order'  => 11,
        'fields' => [
            'url' => [
                'type'         => 'url',
                'label'        => 'Video URL',
                'help'         => 'YouTube, Vimeo, or direct .mp4 link.',
                'translatable' => false,
                'required'     => true,
            ],
            'caption' => [
                'type'         => 'text',
                'label'        => 'Caption',
                'translatable' => true,
                'required'     => false,
            ],
        ],
    ],

    // ── Layout ────────────────────────────────────────────────────────────────

    'divider' => [
        'label'       => 'Divider',
        'description' => 'A horizontal separator between sections.',
        'icon'        => 'minus',
        'category'    => 'layout',
        'sort_order'  => 20,
        'fields' => [
            'style' => [
                'type'         => 'select',
                'label'        => 'Style',
                'translatable' => false,
                'default'      => 'line',
                'options'      => ['line' => 'Line', 'dashed' => 'Dashed', 'dotted' => 'Dotted', 'space' => 'Space only'],
            ],
        ],
    ],

    'alert' => [
        'label'       => 'Alert',
        'description' => 'An info, warning, success, or error notice box.',
        'icon'        => 'exclamation-triangle',
        'category'    => 'layout',
        'sort_order'  => 21,
        'fields' => [
            'type' => [
                'type'         => 'select',
                'label'        => 'Type',
                'translatable' => false,
                'required'     => true,
                'default'      => 'info',
                'options'      => ['info' => 'Info', 'success' => 'Success', 'warning' => 'Warning', 'error' => 'Error'],
                'width'        => 'half',
            ],
            'title' => [
                'type'         => 'text',
                'label'        => 'Title',
                'translatable' => true,
                'required'     => false,
                'width'        => 'half',
            ],
            'message' => [
                'type'         => 'textarea',
                'label'        => 'Message',
                'translatable' => true,
                'required'     => true,
            ],
        ],
    ],

    'accordion' => [
        'label'       => 'Accordion',
        'description' => 'Expandable FAQ-style sections.',
        'icon'        => 'chevron-down',
        'category'    => 'layout',
        'sort_order'  => 22,
        'fields' => [
            'items' => [
                'type'         => 'repeater',
                'label'        => 'Items',
                'translatable' => true,
                'add_label'    => 'Add Item',
                'items' => [
                    'question' => ['type' => 'text',     'label' => 'Question', 'translatable' => true, 'required' => true],
                    'answer'   => ['type' => 'textarea', 'label' => 'Answer',   'translatable' => true, 'required' => true],
                ],
            ],
        ],
    ],

    // ── Advanced ──────────────────────────────────────────────────────────────

    'html' => [
        'label'       => 'HTML',
        'description' => 'Raw HTML embed for advanced use.',
        'icon'        => 'code-bracket-square',
        'category'    => 'advanced',
        'sort_order'  => 30,
        'fields' => [
            'code' => [
                'type'         => 'code',
                'label'        => 'HTML Code',
                'translatable' => false,
                'required'     => true,
            ],
        ],
    ],

];
