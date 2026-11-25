@props(['page', 'current', 'actions' => null])

{{-- Shared header for the page content, settings and media screens: title on the left, all buttons on the right (below on mobile). --}}
<div {{ $attributes->class('mb-6 flex flex-col gap-4 border-b border-zinc-200 pb-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700') }}>
    <div class="flex min-w-0 items-center gap-3">
        <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('admin.pages.index')" wire:navigate :aria-label="__('Back to pages')" />

        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <flux:heading size="xl" level="1" class="truncate">{{ $page->title }}</flux:heading>

                @if ($page->isPublished())
                    <flux:badge size="sm" color="green">{{ __('Published') }}</flux:badge>
                @else
                    <flux:badge size="sm" color="zinc">{{ __('Draft') }}</flux:badge>
                @endif
            </div>
            <flux:text class="truncate font-mono text-xs">/{{ $page->slug }}</flux:text>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <flux:button.group>
            <flux:button
                size="sm"
                icon="squares-2x2"
                :variant="$current === 'content' ? 'filled' : 'outline'"
                :href="route('admin.pages.edit', $page)"
                wire:navigate
                :aria-current="$current === 'content' ? 'page' : null"
            >
                {{ __('Content') }}
            </flux:button>
            <flux:button
                size="sm"
                icon="cog-6-tooth"
                :variant="$current === 'settings' ? 'filled' : 'outline'"
                :href="route('admin.pages.settings', $page)"
                wire:navigate
                :aria-current="$current === 'settings' ? 'page' : null"
            >
                {{ __('Settings') }}
            </flux:button>
            <flux:button
                size="sm"
                icon="photo"
                :variant="$current === 'media' ? 'filled' : 'outline'"
                :href="route('admin.pages.media', $page)"
                wire:navigate
                :aria-current="$current === 'media' ? 'page' : null"
            >
                {{ __('Media') }}
            </flux:button>
        </flux:button.group>

        <flux:button size="sm" variant="ghost" icon="arrow-top-right-on-square" :href="$page->url()" target="_blank">{{ __('View') }}</flux:button>

        {{ $actions ?? '' }}
    </div>
</div>
