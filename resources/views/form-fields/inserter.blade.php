<flux:dropdown position="bottom" align="center">
    @if ($compact)
        <flux:button size="xs" variant="filled" icon="plus" class="rounded-full!" :aria-label="__('Insert field')" />
    @else
        <flux:button icon="plus" data-test="add-field">{{ __('Add field') }}</flux:button>
    @endif

    <flux:menu>
        @foreach (app(App\FormFields\FormFieldRegistry::class)->all() as $fieldType)
            <flux:menu.item
                :icon="$fieldType->icon()"
                wire:click="addField('{{ $fieldType->key() }}'{{ $position === null ? '' : ', '.$position }})"
            >
                {{ $fieldType->label() }}
            </flux:menu.item>
        @endforeach
    </flux:menu>
</flux:dropdown>
