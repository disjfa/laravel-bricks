import { markdownEditor } from './markdown-editor';
import { turnstile } from './turnstile';
import './tom-select';
import './date-picker';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('markdownEditor', markdownEditor);
    window.Alpine.data('turnstile', turnstile);
});
