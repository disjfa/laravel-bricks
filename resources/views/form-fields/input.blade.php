{{-- Receives $type (an InputFormField), $settings and $model (the wire:model path). --}}
<flux:input
    wire:model="{{ $model }}"
    :type="$type->inputType()"
    :label="$settings['label'] ?? ''"
    :placeholder="filled($settings['placeholder'] ?? null) ? $settings['placeholder'] : null"
    :description:trailing="filled($settings['help'] ?? null) ? $settings['help'] : null"
    :required="(bool) ($settings['required'] ?? false)"
/>
