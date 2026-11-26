@props(['menu'])

<flux:navbar {{ $attributes }}>
    @foreach ($menu as $link)
        <flux:navbar.item
            :icon="$link->iconName()"
            :href="$link->url()"
            :current="$link->isCurrent()"
            :target="$link->opensInNewTab() ? '_blank' : null"
            :rel="$link->opensInNewTab() ? 'noopener' : null"
        >
            {{ $link->text() }}
        </flux:navbar.item>
    @endforeach
</flux:navbar>
