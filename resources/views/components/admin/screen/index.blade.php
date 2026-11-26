@props([
    'title',
    'back' => null,
    'subtitle' => null,
    'subtitleUrl' => null,
    'tabs' => [],
    'viewUrl' => null,
    'save' => null,
    'columns' => false,
    'badge' => null,
    'actions' => null,
])

{{--
    Every admin screen that edits one thing (a page, blog, form, menu or the site settings) uses this layout,
    so the header, its line and the padding are the same everywhere.

    - tabs: array<int, array{label: string, icon: string, url: string, current: bool}>
    - save: the Livewire action of the Save button (accesskey S), or null for no Save button.
    - columns: two columns that scroll on their own, like the editor with its live preview. Fill them with
      <x-admin.screen.column>. Otherwise the content scrolls below the header.

    From lg up the screen fills the viewport, so the header with its buttons always stays in view.
--}}
<div data-full-bleed {{ $attributes->class('lg:flex lg:h-dvh lg:flex-col') }}>
    <header class="z-20 bg-(--site-background) px-4 pt-4" data-test="screen-header">
        <div class="flex flex-col gap-4 border-b border-zinc-200 pb-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700">
            <div class="flex min-w-0 items-center gap-3">
                @if ($back)
                    <flux:button variant="ghost" size="sm" icon="arrow-left" :href="$back" wire:navigate :aria-label="__('Back')" />
                @endif

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <flux:heading size="xl" level="1" class="truncate">{{ $title }}</flux:heading>
                        {{ $badge ?? '' }}
                    </div>

                    @if (filled($subtitle))
                        @if ($subtitleUrl)
                            <a href="{{ $subtitleUrl }}" target="_blank" class="block truncate font-mono text-xs text-zinc-500 hover:text-zinc-800 hover:underline dark:text-white/70 dark:hover:text-white" data-test="public-url">{{ $subtitle }}</a>
                        @else
                            <flux:text class="truncate font-mono text-xs">{{ $subtitle }}</flux:text>
                        @endif
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($tabs !== [])
                    <flux:button.group>
                        @foreach ($tabs as $tab)
                            <flux:button
                                size="sm"
                                :icon="$tab['icon']"
                                :variant="$tab['current'] ? 'filled' : 'outline'"
                                :href="$tab['url']"
                                wire:navigate
                                :aria-current="$tab['current'] ? 'page' : null"
                            >
                                {{ $tab['label'] }}
                            </flux:button>
                        @endforeach
                    </flux:button.group>
                @endif

                @if ($viewUrl)
                    <flux:button size="sm" variant="ghost" icon="arrow-top-right-on-square" :href="$viewUrl" target="_blank">{{ __('View') }}</flux:button>
                @endif

                {{ $actions ?? '' }}

                @if ($save)
                    <flux:button
                        size="sm"
                        variant="primary"
                        icon="check"
                        wire:click="{{ $save }}"
                        accesskey="s"
                        :title="__('Save (accesskey S)')"
                        data-test="save-button"
                    >
                        {{ __('Save') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </header>

    @if ($columns)
        <div class="grid lg:min-h-0 lg:flex-1 lg:grid-cols-2">
            {{ $slot }}
        </div>
    @else
        <div class="p-4 lg:min-h-0 lg:flex-1 lg:overflow-y-auto lg:overscroll-contain">
            {{ $slot }}
        </div>
    @endif
</div>
