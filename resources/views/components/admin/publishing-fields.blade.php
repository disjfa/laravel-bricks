@props(['name' => 'publishing'])

{{-- The fields of App\Livewire\Forms\PublishingForm, $name is the form property on the component. --}}
<div {{ $attributes->class('grid gap-6 sm:grid-cols-2') }}>
    <flux:select wire:model="{{ $name }}.status" :label="__('Status')" data-test="publishing-status">
        @foreach (App\Enums\PublishStatus::cases() as $status)
            <flux:select.option :value="$status->value">{{ $status->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:input type="number" min="0" wire:model="{{ $name }}.seqnr" :label="__('Sequence number')" :description:trailing="__('Orders the items, lower comes first.')" />

    <flux:input type="datetime-local" wire:model="{{ $name }}.publishedAt" :label="__('Publish date')" :description:trailing="__('Leave empty to publish right away.')" />

    <flux:input type="datetime-local" wire:model="{{ $name }}.publishedUntil" :label="__('Publish until')" :description:trailing="__('Leave empty to keep it online.')" />
</div>
