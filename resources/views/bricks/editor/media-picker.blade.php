{{-- Pick an image from the media panel. Expects $field (the wire:model path), $imageMedia and $mediaUrl (the media panel). --}}
<flux:dropdown position="bottom" align="end">
    <flux:button icon="photo" :aria-label="__('Choose from media')" />

    <flux:menu class="max-h-80 w-72 overflow-y-auto">
        @forelse ($imageMedia as $item)
            <flux:menu.item wire:click="$set({{ Js::from($field) }}, {{ Js::from($item['url']) }})" wire:key="pick-{{ $field }}-{{ $item['id'] }}">
                <img src="{{ $item['thumb'] }}" alt="" class="me-2 size-10 rounded object-cover">
                <span class="truncate">{{ $item['name'] }}</span>
            </flux:menu.item>
        @empty
            <flux:menu.item icon="arrow-up-tray" :href="$mediaUrl" wire:navigate>
                {{ __('Upload images in the media panel') }}
            </flux:menu.item>
        @endforelse
    </flux:menu>
</flux:dropdown>
