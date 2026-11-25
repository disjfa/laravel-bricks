<flux:select wire:model.live="{{ $path }}" :label="$field->getLabel()" :description:trailing="$field->getHelp()">
    @foreach ($field->getOptions() as $optionValue => $optionLabel)
        <flux:select.option :value="$optionValue">{{ $optionLabel }}</flux:select.option>
    @endforeach
</flux:select>
