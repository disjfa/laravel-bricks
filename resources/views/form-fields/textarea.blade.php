{{-- Receives $type, $settings and $model (the wire:model path). --}}
<flux:textarea
    wire:model="{{ $model }}"
    :label="$settings['label'] ?? ''"
    :placeholder="filled($settings['placeholder'] ?? null) ? $settings['placeholder'] : null"
    :description:trailing="filled($settings['help'] ?? null) ? $settings['help'] : null"
    :required="(bool) ($settings['required'] ?? false)"
    rows="5"
/>
