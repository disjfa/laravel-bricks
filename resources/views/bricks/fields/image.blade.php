{{-- Receives $field, $path, $value, $imageMedia and $page. --}}
<flux:field>
    <flux:label>{{ $field->getLabel() }}</flux:label>

    <div class="flex gap-2">
        <flux:input wire:model.live.debounce.400ms="{{ $path }}" :placeholder="$field->getPlaceholder()" />
        @include('bricks.editor.media-picker', ['field' => $path])

        @if (filled($value))
            <flux:button icon="x-mark" variant="ghost" wire:click="$set({{ Js::from($path) }}, '')" :aria-label="__('Remove image')" />
        @endif
    </div>

    @if (filled($field->getHelp()))
        <flux:description>{{ $field->getHelp() }}</flux:description>
    @endif

    <flux:error :name="$path" />

    @if (filled($value))
        <img src="{{ $value }}" alt="" class="mt-2 max-h-40 rounded-lg object-cover">
    @endif
</flux:field>
