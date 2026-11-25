<flux:textarea
    wire:model.live.debounce.400ms="{{ $path }}"
    :label="$field->getLabel()"
    :placeholder="$field->getPlaceholder()"
    :description:trailing="$field->getHelp()"
    :rows="$field->getRows()"
/>
