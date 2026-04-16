{{--
 | Tiptap-based rich text editor (loaded once per page from admin/layout.blade.php).
 |
 | Fire from any component:
 |   <textarea name="..." x-data x-init="initRTE($el)" rows="6">{{ $html }}</textarea>
 |
 | The ES module below defines:
 |   window.initRTE(textarea) — attaches a Tiptap editor to the given textarea,
 |     hides the textarea, renders the editor + toolbar above, and syncs HTML
 |     back into the textarea on every change so form submission carries the
 |     latest content.
--}}

<script type="importmap">
{
    "imports": {
        "@tiptap/core":          "https://esm.sh/@tiptap/core@2.11.5",
        "@tiptap/starter-kit":   "https://esm.sh/@tiptap/starter-kit@2.11.5",
        "@tiptap/extension-link":"https://esm.sh/@tiptap/extension-link@2.11.5"
    }
}
</script>

<style>
    .rte-wrapper       { border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; background: #fff; }
    .rte-wrapper:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.2); }
    .rte-toolbar       { display: flex; flex-wrap: wrap; gap: 2px; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; background: #fafafa; }
    .rte-btn           { width: 32px; height: 32px; border: none; background: transparent; color: #374151; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; }
    .rte-btn:hover     { background: #e5e7eb; }
    .rte-btn.active    { background: #dbeafe; color: #1d4ed8; }
    .rte-btn[disabled] { opacity: 0.4; cursor: not-allowed; }
    .rte-divider       { width: 1px; background: #e5e7eb; margin: 4px 2px; }
    .rte-content       { padding: 12px 14px; min-height: 160px; font-size: 15px; line-height: 1.65; color: #111827; outline: none; }
    .rte-content p     { margin: 0 0 0.8em; }
    .rte-content p:last-child { margin-bottom: 0; }
    .rte-content h2    { font-size: 1.5rem; font-weight: 600; margin: 1em 0 0.5em; }
    .rte-content h3    { font-size: 1.2rem; font-weight: 600; margin: 0.8em 0 0.4em; }
    .rte-content ul    { list-style: disc; padding-left: 1.5rem; margin: 0.5em 0; }
    .rte-content ol    { list-style: decimal; padding-left: 1.5rem; margin: 0.5em 0; }
    .rte-content blockquote { border-left: 3px solid #d1d5db; padding-left: 1rem; margin: 0.75em 0; color: #6b7280; }
    .rte-content code  { background: #f3f4f6; color: #be185d; padding: 1px 5px; border-radius: 3px; font-size: 0.9em; }
    .rte-content pre   { background: #1f2937; color: #f9fafb; padding: 12px 14px; border-radius: 6px; overflow-x: auto; font-size: 0.9rem; margin: 0.75em 0; }
    .rte-content pre code { background: transparent; color: inherit; padding: 0; }
    .rte-content a     { color: #2563eb; text-decoration: underline; }
    .rte-content hr    { border: 0; border-top: 1px solid #e5e7eb; margin: 1.25em 0; }
    .rte-content:empty::before { content: attr(data-placeholder); color: #9ca3af; pointer-events: none; }
</style>

<script type="module">
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';

window.initRTE = function (textarea) {
    if (textarea.dataset.rteInitialized) return;
    textarea.dataset.rteInitialized = '1';

    // Build wrapper
    const wrapper = document.createElement('div');
    wrapper.className = 'rte-wrapper';

    const toolbar = document.createElement('div');
    toolbar.className = 'rte-toolbar';

    const mount = document.createElement('div');
    mount.className = 'rte-content';

    wrapper.appendChild(toolbar);
    wrapper.appendChild(mount);
    textarea.parentNode.insertBefore(wrapper, textarea);
    textarea.style.display = 'none';

    // Build the editor
    const editor = new Editor({
        element: mount,
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
            }),
            Link.configure({ openOnClick: false, autolink: true }),
        ],
        content: textarea.value || '',
        onUpdate({ editor }) {
            textarea.value = editor.getHTML();
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        },
    });

    // Toolbar buttons
    const btn = (label, icon, cmd, isActiveCheck) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'rte-btn';
        b.title = label;
        b.innerHTML = `<i class="bi ${icon}"></i>`;
        b.addEventListener('click', () => { cmd(); b.focus(); });

        if (isActiveCheck) {
            editor.on('selectionUpdate', () => b.classList.toggle('active', isActiveCheck()));
            editor.on('transaction',     () => b.classList.toggle('active', isActiveCheck()));
        }
        return b;
    };

    const divider = () => {
        const d = document.createElement('span');
        d.className = 'rte-divider';
        return d;
    };

    toolbar.append(
        btn('Bold',     'bi-type-bold',     () => editor.chain().focus().toggleBold().run(),     () => editor.isActive('bold')),
        btn('Italic',   'bi-type-italic',   () => editor.chain().focus().toggleItalic().run(),   () => editor.isActive('italic')),
        btn('Strike',   'bi-type-strikethrough', () => editor.chain().focus().toggleStrike().run(), () => editor.isActive('strike')),
        btn('Code',     'bi-code',          () => editor.chain().focus().toggleCode().run(),     () => editor.isActive('code')),
        divider(),
        btn('Heading 2','bi-type-h2',       () => editor.chain().focus().toggleHeading({ level: 2 }).run(), () => editor.isActive('heading', { level: 2 })),
        btn('Heading 3','bi-type-h3',       () => editor.chain().focus().toggleHeading({ level: 3 }).run(), () => editor.isActive('heading', { level: 3 })),
        divider(),
        btn('Bullet list',  'bi-list-ul', () => editor.chain().focus().toggleBulletList().run(),  () => editor.isActive('bulletList')),
        btn('Ordered list', 'bi-list-ol', () => editor.chain().focus().toggleOrderedList().run(), () => editor.isActive('orderedList')),
        btn('Quote',        'bi-quote',   () => editor.chain().focus().toggleBlockquote().run(),  () => editor.isActive('blockquote')),
        btn('Code block',   'bi-code-slash', () => editor.chain().focus().toggleCodeBlock().run(), () => editor.isActive('codeBlock')),
        divider(),
        btn('Link', 'bi-link-45deg', () => {
            const prev = editor.getAttributes('link').href;
            const url = window.prompt('URL (leave empty to remove)', prev || '');
            if (url === null) return;
            if (url === '') return editor.chain().focus().extendMarkRange('link').unsetLink().run();
            editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
        }, () => editor.isActive('link')),
        btn('Horizontal rule', 'bi-dash-lg', () => editor.chain().focus().setHorizontalRule().run()),
        divider(),
        btn('Undo', 'bi-arrow-counterclockwise', () => editor.chain().focus().undo().run()),
        btn('Redo', 'bi-arrow-clockwise',        () => editor.chain().focus().redo().run()),
    );
};
</script>
