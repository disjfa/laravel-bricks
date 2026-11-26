import { markdownEditor } from './markdown-editor';
import './tom-select';
import './date-picker';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('markdownEditor', markdownEditor);
});
