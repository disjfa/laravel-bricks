{{-- Receives $type (a SelectField), $settings and $model (the wire:model path). --}}
<flux:select
    wire:model="{{ $model }}"
    :label="$settings['label'] ?? ''"
    :placeholder="filled($settings['placeholder'] ?? null) ? $settings['placeholder'] : __('Choose...')"
    :description:trailing="filled($settings['help'] ?? null) ? $settings['help'] : null"
    :required="(bool) ($settings['required'] ?? false)"
>
    @foreach ($type->options($settings) as $option)
        <flux:select.option :value="$option">{{ $option }}</flux:select.option>
    @endforeach
</flux:select>
