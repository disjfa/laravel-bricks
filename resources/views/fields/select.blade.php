{{-- Receives $field, $path (the wire:model path) and $value. A segmented select shows its options side by side. --}}
@if ($field->isSegmented())
    <flux:radio.group wire:model.live="{{ $path }}" :label="$field->getLabel()" :description:trailing="$field->getHelp()" variant="segmented">
        @foreach ($field->getOptions() as $optionValue => $optionLabel)
            <flux:radio :value="$optionValue" :label="$optionLabel" />
        @endforeach
    </flux:radio.group>
@else
    <flux:select wire:model.live="{{ $path }}" :label="$field->getLabel()" :description:trailing="$field->getHelp()">
        @foreach ($field->getOptions() as $optionValue => $optionLabel)
            <flux:select.option :value="$optionValue">{{ $optionLabel }}</flux:select.option>
        @endforeach
    </flux:select>
@endif
