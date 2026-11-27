{{-- Receives $type, $settings and $model (the wire:model path). --}}
<flux:field variant="inline">
    <flux:checkbox wire:model="{{ $model }}" />
    <flux:label>{{ $settings['label'] ?? '' }}@if ($settings['required'] ?? false) <span aria-hidden="true">*</span>@endif</flux:label>

    @if (filled($settings['help'] ?? null))
        <flux:description>{{ $settings['help'] }}</flux:description>
    @endif

    <flux:error :name="$model" />
</flux:field>
