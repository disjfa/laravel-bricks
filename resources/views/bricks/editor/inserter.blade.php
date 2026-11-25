<flux:dropdown position="bottom" align="center">
    @if ($compact)
        <flux:button size="xs" variant="filled" icon="plus" class="rounded-full!" :aria-label="__('Insert brick')" />
    @else
        <flux:button icon="plus" data-test="add-brick">{{ __('Add brick') }}</flux:button>
    @endif

    <flux:menu>
        @foreach (app(App\Bricks\BrickRegistry::class)->all() as $brickType)
            <flux:menu.item
                :icon="$brickType->icon()"
                wire:click="addBrick('{{ $brickType->key() }}'{{ $position === null ? '' : ', '.$position }})"
            >
                {{ $brickType->label() }}
            </flux:menu.item>
        @endforeach
    </flux:menu>
</flux:dropdown>
