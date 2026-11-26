import { markdownEditor } from './markdown-editor';
import { turnstile } from './turnstile';
import { tagSelect } from './tom-select';
import './date-picker';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('markdownEditor', markdownEditor);
    window.Alpine.data('turnstile', turnstile);
    window.Alpine.data('tagSelect', tagSelect);
});
