@props(['menu'])

@foreach ($menu as $link)
    <flux:sidebar.item
        :icon="$link->iconName()"
        :href="$link->url()"
        :current="$link->isCurrent()"
        wire:navigate
    >
        {{ $link->text() }}
    </flux:sidebar.item>
@endforeach
