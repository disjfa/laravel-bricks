{{-- Receives $field, $path (the wire:model path) and $value. --}}
<flux:switch wire:model.live="{{ $path }}" :label="$field->getLabel()" :description="$field->getHelp()" align="left" />
