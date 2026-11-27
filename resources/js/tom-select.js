import TomSelect from 'tom-select';

/**
 * Turns every Flux select (<flux:select> renders a native select with data-flux-select-native) into a
 * Tom Select. Add data-native to a flux:select to keep the browser's own select.
 *
 * Tom Select keeps the original select in sync and fires "change" on it, so wire:model keeps working.
 * Livewire morphs the DOM to match the server HTML, which would remove the Tom Select wrapper and the
 * classes that hide the original select; the morph hook below prevents that and syncs the options instead.
 */
const selector = 'select[data-flux-select-native]:not([data-native])';

/**
 * Lists with more options than this get a search field.
 */
const searchFrom = 8;

function enhance(root = document) {
    if (!root.querySelectorAll) {
        return;
    }

    const selects = root.matches?.(selector) ? [root] : root.querySelectorAll(selector);

    selects.forEach((select) => {
        if (select.tomselect || select.multiple) {
            return;
        }

        const tomSelect = new TomSelect(select, {
            maxOptions: null,
            // Options with an empty value can be chosen, like "System font". Flux placeholders are disabled and hidden.
            allowEmptyOption: true,
            // A short list does not need a search field, which also keeps the keyboard closed on phones.
            controlInput: select.options.length > searchFrom ? '<input type="text" autocomplete="off" size="1" />' : null,
        });

        tomSelect.optionsSignature = optionsSignature(select);
    });
}

function optionsSignature(select) {
    return Array.from(select.options)
        .map((option) => `${option.value}\u0000${option.text}`)
        .join('\u0001');
}

/**
 * Take over options and the value from the select when the server changed them. Tom Select's own
 * sync is silent, so this does not send the value back to Livewire.
 */
function sync(select) {
    const tomSelect = select.tomselect;
    const signature = optionsSignature(select);

    if (tomSelect.optionsSignature !== signature) {
        tomSelect.optionsSignature = signature;
        tomSelect.clearOptions();
        tomSelect.sync();

        return;
    }

    if (tomSelect.getValue() !== select.value) {
        tomSelect.setValue(select.value, true);
    }
}

function syncAll(root) {
    root.querySelectorAll?.(selector).forEach((select) => {
        if (select.tomselect) {
            sync(select);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => enhance());
document.addEventListener('livewire:navigated', () => enhance());

document.addEventListener('livewire:init', () => {
    const Livewire = window.Livewire;

    Livewire.hook('morph.updating', ({ el, toEl, skip }) => {
        // On the parent of a Tom Select: take the wrapper out, so its children line up with the
        // server HTML. Otherwise the wrapper would be matched with the next element, like an error.
        // Only the wrappers of Flux selects: a tag select (tagSelect below) lives in wire:ignore and is left alone.
        const wrapper = el.querySelector?.(':scope > .ts-wrapper');

        if (wrapper && wrapper.previousElementSibling?.matches(selector)) {
            wrapper.hadFocus = wrapper.contains(document.activeElement);
            wrapper.remove();
        }

        if (!el.tomselect) {
            return;
        }

        // On the select itself: keep the attributes Tom Select set, take over the options from the
        // server and put the wrapper back. The morph already moved on to the next sibling.
        if (el.innerHTML !== toEl.innerHTML) {
            const value = el.value;

            el.replaceChildren(...Array.from(toEl.cloneNode(true).childNodes));
            el.value = value;
        }

        const tomSelectWrapper = el.tomselect.wrapper;
        el.after(tomSelectWrapper);

        if (tomSelectWrapper.hadFocus) {
            el.tomselect.focus();
        }

        skip();
    });

    // Pick up new selects after a morph.
    Livewire.hook('morphed', ({ el }) => enhance(el));

    // wire:model sets the value of the select after the morph, sync once the update is rendered.
    Livewire.interceptMessage(({ message, onSuccess }) => {
        onSuccess(({ onRender }) => {
            onRender(() => {
                enhance(message.component.el);
                syncAll(message.component.el);
            });
        });
    });
});

/**
 * A multiple select with Tom Select for tags or categories: x-data="tagSelect('categories')" on an element with
 * wire:ignore that holds a <select multiple x-ref="select">. Typing a name that is not in the list adds it.
 * The chosen names are written to the given property of the Livewire component, without a request.
 */
export function tagSelect(property) {
    return {
        tomSelect: null,

        init() {
            this.tomSelect = new TomSelect(this.$refs.select, {
                plugins: ['remove_button'],
                create: true,
                createOnBlur: true,
                persist: false,
                maxOptions: null,
                onChange: () => this.$wire.set(property, this.tomSelect.getValue(), false),
            });
        },

        destroy() {
            this.tomSelect?.destroy();
        },
    };
}
