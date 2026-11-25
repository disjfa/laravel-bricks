{{-- Receives $field, $path (the wire:model path) and $value. --}}
<flux:input
    wire:model.live.debounce.400ms="{{ $path }}"
    :label="$field->getLabel()"
    :placeholder="$field->getPlaceholder()"
    :description:trailing="$field->getHelp()"
/>
