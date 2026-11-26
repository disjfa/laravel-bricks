import { Calendar, time as timeExtension } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/core.css';
import 'vanilla-calendar-pro/styles/time.css';

/**
 * Turns every Flux date and datetime input into a text input with a Vanilla Calendar Pro popup.
 * Add data-native to the input to keep the browser's own picker.
 *
 * The input keeps a plain value Laravel can parse: "2030-01-31" for dates and "2030-01-31 09:00"
 * for dates with a time. Changes fire "input" and "change", so wire:model keeps working.
 */
const selector = 'input[data-flux-control][type="date"]:not([data-native]), input[data-flux-control][type="datetime-local"]:not([data-native])';

function parse(value) {
    const [date = '', time = ''] = (value || '').trim().split(/[T ]/);

    return {
        date: /^\d{4}-\d{2}-\d{2}$/.test(date) ? date : null,
        time: /^\d{2}:\d{2}/.test(time) ? time.slice(0, 5) : null,
    };
}

function theme() {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

function enhance(root = document) {
    if (!root.querySelectorAll) {
        return;
    }

    root.querySelectorAll(selector).forEach((input) => {
        if (input.calendar) {
            return;
        }

        const withTime = input.type === 'datetime-local';
        const { date: initialDate, time: initialTime } = parse(input.value);

        input.type = 'text';
        // A datetime-local input writes its value with a "T", show the same format the picker writes.
        input.value = initialDate ? (withTime && initialTime ? `${initialDate} ${initialTime}` : initialDate) : '';
        input.dataset.calendar = withTime ? 'datetime' : 'date';
        input.autocomplete = 'off';
        input.placeholder ||= withTime ? 'yyyy-mm-dd hh:mm' : 'yyyy-mm-dd';

        try {
            createCalendar(input, withTime, initialDate, initialTime);
        } catch (error) {
            // Keep the other date fields working, this one stays a plain text field.
            console.error('Could not create the date picker', input, error);
        }
    });
}

/**
 * @param {HTMLInputElement} input
 * @param {boolean} withTime
 * @param {string|null} initialDate
 * @param {string|null} initialTime
 */
function createCalendar(input, withTime, initialDate, initialTime) {
    const calendar = new Calendar(input, {
        extensions: [timeExtension],
        inputMode: true,
        positionToInput: 'auto',
        selectedTheme: theme(),
        firstWeekday: 1,
        locale: document.documentElement.lang || 'en',
        selectionTimeMode: withTime ? 24 : false,
        selectedDates: initialDate ? [initialDate] : [],
        selectedTime: initialTime ?? '09:00',
        enableDateToggle: true,
        onChangeToInput(self) {
            const [selectedDate] = self.context.selectedDates;

            input.value = selectedDate
                ? (withTime ? `${selectedDate} ${self.context.selectedTime || '09:00'}` : selectedDate)
                : '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));

            if (!withTime) {
                self.hide();
            }
        },
        onShow(self) {
            self.set({ selectedTheme: theme() }, { dates: false, month: false, year: false, time: false });
        },
    });

    calendar.init();
    input.calendar = calendar;
    input.calendarValue = input.value;
}

/**
 * Show a value the server set (like a reset field) in the calendar.
 */
function syncAll(root) {
    root.querySelectorAll?.('input[data-calendar]').forEach((input) => {
        if (!input.calendar || input.calendarValue === input.value) {
            return;
        }

        const { date: newDate, time: newTime } = parse(input.value);

        input.calendarValue = input.value;
        input.calendar.set({ selectedDates: newDate ? [newDate] : [], ...(newTime ? { selectedTime: newTime } : {}) });
    });
}

document.addEventListener('DOMContentLoaded', () => enhance());
document.addEventListener('livewire:navigated', () => enhance());

document.addEventListener('livewire:init', () => {
    const Livewire = window.Livewire;

    // Keep the input a text input with the attributes the calendar added, the server renders a date input.
    Livewire.hook('morph.updating', ({ el, toEl }) => {
        if (!el.calendar || toEl.nodeType !== 1) {
            return;
        }

        toEl.setAttribute('type', 'text');

        for (const attribute of Array.from(el.attributes)) {
            if (!toEl.hasAttribute(attribute.name) && (attribute.name.startsWith('data-vc') || ['data-calendar', 'autocomplete', 'placeholder', 'readonly'].includes(attribute.name))) {
                toEl.setAttribute(attribute.name, attribute.value);
            }
        }
    });

    Livewire.hook('morphed', ({ el }) => enhance(el));

    Livewire.hook('commit', ({ component, succeed }) => {
        succeed(() => requestAnimationFrame(() => {
            enhance(component.el);
            syncAll(component.el);
        }));
    });
});
