import EasyMDE from 'easymde';

/**
 * Heroicons (mini), the same set Flux uses, so EasyMDE does not need to download Font Awesome.
 */
const icons = {
    'bold': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-4"><path fill-rule="evenodd" d="M4 3a1 1 0 0 1 1-1h6a4.5 4.5 0 0 1 3.274 7.587A4.75 4.75 0 0 1 11.25 18H5a1 1 0 0 1-1-1V3Zm2.5 5.5v-4H11a2 2 0 1 1 0 4H6.5Zm0 2.5v4.5h4.75a2.25 2.25 0 0 0 0-4.5H6.5Z" clip-rule="evenodd"/></svg>',
    'italic': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-4"><path fill-rule="evenodd" d="M8 2.75A.75.75 0 0 1 8.75 2h7.5a.75.75 0 0 1 0 1.5h-3.215l-4.483 13h2.698a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1 0-1.5h3.215l4.483-13H8.75A.75.75 0 0 1 8 2.75Z" clip-rule="evenodd"/></svg>',
    'h2': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-4"><path fill-rule="evenodd" d="M2.75 4a.75.75 0 0 1 .75.75v4.5h5v-4.5a.75.75 0 0 1 1.5 0v10.5a.75.75 0 0 1-1.5 0v-4.5h-5v4.5a.75.75 0 0 1-1.5 0V4.75A.75.75 0 0 1 2.75 4ZM15 9.5c-.729 0-1.445.051-2.146.15a.75.75 0 0 1-.208-1.486 16.887 16.887 0 0 1 3.824-.1c.855.074 1.512.78 1.527 1.637a17.476 17.476 0 0 1-.009.931 1.713 1.713 0 0 1-1.18 1.556l-2.453.818a1.25 1.25 0 0 0-.855 1.185v.309h3.75a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75v-1.059a2.75 2.75 0 0 1 1.88-2.608l2.454-.818c.102-.034.153-.117.155-.188a15.556 15.556 0 0 0 .009-.85.171.171 0 0 0-.158-.169A15.458 15.458 0 0 0 15 9.5Z" clip-rule="evenodd"/></svg>',
    'h3': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-4"><path fill-rule="evenodd" d="M2.75 4a.75.75 0 0 1 .75.75v4.5h5v-4.5a.75.75 0 0 1 1.5 0v10.5a.75.75 0 0 1-1.5 0v-4.5h-5v4.5a.75.75 0 0 1-1.5 0V4.75A.75.75 0 0 1 2.75 4ZM15 9.5c-.73 0-1.448.051-2.15.15a.75.75 0 1 1-.209-1.485 16.886 16.886 0 0 1 3.476-.128c.985.065 1.878.837 1.883 1.932V10a6.75 6.75 0 0 1-.301 2A6.75 6.75 0 0 1 18 14v.031c-.005 1.095-.898 1.867-1.883 1.932a17.018 17.018 0 0 1-3.467-.127.75.75 0 0 1 .209-1.485 15.377 15.377 0 0 0 3.16.115c.308-.02.48-.24.48-.441L16.5 14c0-.431-.052-.85-.15-1.25h-2.6a.75.75 0 0 1 0-1.5h2.6c.098-.4.15-.818.15-1.25v-.024c-.001-.201-.173-.422-.481-.443A15.485 15.485 0 0 0 15 9.5Z" clip-rule="evenodd"/></svg>',
    'chat-bubble-bottom-center-text': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-4"><path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 4.014 1 5.426v5.148c0 1.413.993 2.67 2.43 2.902 1.168.188 2.352.327 3.55.414.28.02.521.18.642.413l1.713 3.293a.75.75 0 0 0 1.33 0l1.713-3.293a.783.783 0 0 1 .642-.413 41.102 41.102 0 0 0 3.55-.414c1.437-.231 2.43-1.49 2.43-2.902V5.426c0-1.413-.993-2.67-2.43-2.902A41.289 41.289 0 0 0 10 2ZM6.75 6a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 2.5a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z" clip-rule="evenodd"/></svg>',
    'list-bullet': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-4"><path fill-rule="evenodd" d="M6 4.75A.75.75 0 0 1 6.75 4h10.5a.75.75 0 0 1 0 1.5H6.75A.75.75 0 0 1 6 4.75ZM6 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H6.75A.75.75 0 0 1 6 10Zm0 5.25a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H6.75a.75.75 0 0 1-.75-.75ZM1.99 4.75a1 1 0 0 1 1-1H3a1 1 0 0 1 1 1v.01a1 1 0 0 1-1 1h-.01a1 1 0 0 1-1-1v-.01ZM1.99 15.25a1 1 0 0 1 1-1H3a1 1 0 0 1 1 1v.01a1 1 0 0 1-1 1h-.01a1 1 0 0 1-1-1v-.01ZM1.99 10a1 1 0 0 1 1-1H3a1 1 0 0 1 1 1v.01a1 1 0 0 1-1 1h-.01a1 1 0 0 1-1-1V10Z" clip-rule="evenodd"/></svg>',
    'numbered-list': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-4"><path d="M3 1.25a.75.75 0 0 0 0 1.5h.25v2.5a.75.75 0 0 0 1.5 0V2A.75.75 0 0 0 4 1.25H3ZM2.97 8.654a3.5 3.5 0 0 1 1.524-.12.034.034 0 0 1-.012.012L2.415 9.579A.75.75 0 0 0 2 10.25v1c0 .414.336.75.75.75h2.5a.75.75 0 0 0 0-1.5H3.927l1.225-.613c.52-.26.848-.79.848-1.371 0-.647-.429-1.327-1.193-1.451a5.03 5.03 0 0 0-2.277.155.75.75 0 0 0 .44 1.434ZM7.75 3a.75.75 0 0 0 0 1.5h9.5a.75.75 0 0 0 0-1.5h-9.5ZM7.75 9.25a.75.75 0 0 0 0 1.5h9.5a.75.75 0 0 0 0-1.5h-9.5ZM7.75 15.5a.75.75 0 0 0 0 1.5h9.5a.75.75 0 0 0 0-1.5h-9.5ZM2.625 13.875a.75.75 0 0 0 0 1.5h1.5a.125.125 0 0 1 0 .25H3.5a.75.75 0 0 0 0 1.5h.625a.125.125 0 0 1 0 .25h-1.5a.75.75 0 0 0 0 1.5h1.5a1.625 1.625 0 0 0 1.37-2.5 1.625 1.625 0 0 0-1.37-2.5h-1.5Z"/></svg>',
    'link': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-4"><path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z"/><path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z"/></svg>',
    'photo': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-4"><path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909.47.47a.75.75 0 1 1-1.06 1.06L6.53 8.091a.75.75 0 0 0-1.06 0l-2.97 2.97ZM12 7a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z" clip-rule="evenodd"/></svg>',
};

/**
 * Markdown field of the brick editor.
 *
 * The editor lives inside a wire:ignore element, so Livewire does not touch it while morphing.
 * The wire:model path is read from the root's data-path on every change, because the path
 * changes when bricks are reordered (bricks.0.data.body becomes bricks.1.data.body).
 */
export function markdownEditor() {
    return {
        editor: null,
        syncTimeout: null,
        imagePickerOpen: false,

        init() {
            this.editor = new EasyMDE({
                element: this.$refs.input,
                autoDownloadFontAwesome: false,
                spellChecker: false,
                nativeSpellcheck: true,
                status: false,
                forceSync: true,
                minHeight: '8rem',
                toolbar: [
                    { name: 'bold', action: EasyMDE.toggleBold, title: 'Bold', icon: icons['bold'] },
                    { name: 'italic', action: EasyMDE.toggleItalic, title: 'Italic', icon: icons['italic'] },
                    '|',
                    { name: 'heading-2', action: EasyMDE.toggleHeading2, title: 'Heading 2', icon: icons['h2'] },
                    { name: 'heading-3', action: EasyMDE.toggleHeading3, title: 'Heading 3', icon: icons['h3'] },
                    '|',
                    { name: 'quote', action: EasyMDE.toggleBlockquote, title: 'Quote', icon: icons['chat-bubble-bottom-center-text'] },
                    { name: 'unordered-list', action: EasyMDE.toggleUnorderedList, title: 'Bullet list', icon: icons['list-bullet'] },
                    { name: 'ordered-list', action: EasyMDE.toggleOrderedList, title: 'Numbered list', icon: icons['numbered-list'] },
                    '|',
                    { name: 'link', action: EasyMDE.drawLink, title: 'Link', icon: icons['link'] },
                    { name: 'image', action: () => this.toggleImagePicker(), title: 'Image from media', icon: icons['photo'] },
                ],
            });

            this.editor.codemirror.on('change', () => {
                clearTimeout(this.syncTimeout);

                this.syncTimeout = setTimeout(() => {
                    this.$wire.set(this.$root.dataset.path, this.editor.value());
                }, 400);
            });
        },

        toggleImagePicker() {
            this.imagePickerOpen = !this.imagePickerOpen;
        },

        /**
         * Insert an image at the cursor. The URL goes between <> so file names with spaces still work.
         */
        insertImage(url, name) {
            const alt = name.replace(/[\[\]]/g, '');

            this.editor.codemirror.replaceSelection(`![${alt}](<${url}>)`);
            this.editor.codemirror.focus();
            this.imagePickerOpen = false;
        },

        destroy() {
            clearTimeout(this.syncTimeout);
            this.editor?.toTextArea();
            this.editor = null;
        },
    };
}
