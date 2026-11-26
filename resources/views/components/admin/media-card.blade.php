@props(['media', 'showOwner' => false])

{{-- One media library file with copy URL and delete. The surrounding Livewire component handles deleteMedia(id). --}}
<flux:card {{ $attributes->class('flex flex-col overflow-hidden p-0!') }}>
    <a href="{{ $media->getUrl() }}" target="_blank" class="block aspect-4/3 bg-zinc-100 dark:bg-zinc-800">
        @if ($media->hasGeneratedConversion('thumb'))
            <img src="{{ $media->getUrl('thumb') }}" alt="{{ $media->name }}" class="h-full w-full object-cover" loading="lazy">
        @else
            <div class="flex h-full items-center justify-center">
                <flux:icon name="document" class="size-12 text-zinc-400" />
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col gap-3 p-3">
        <div class="min-w-0">
            <flux:heading class="truncate" :title="$media->file_name">{{ $media->name }}</flux:heading>
            <flux:text size="sm">{{ $media->human_readable_size }} &middot; {{ strtoupper($media->extension) }}</flux:text>

            @if ($showOwner)
                @if ($media->model instanceof App\Models\Page)
                    <flux:link size="sm" :href="route('admin.pages.media', $media->model)" wire:navigate class="mt-1 flex items-center gap-1 text-sm">
                        <flux:icon name="document-text" variant="micro" />
                        <span class="truncate">{{ $media->model->title }}</span>
                    </flux:link>
                @elseif ($media->model instanceof App\Models\Blog)
                    <flux:link size="sm" :href="route('admin.blogs.media', $media->model)" wire:navigate class="mt-1 flex items-center gap-1 text-sm">
                        <flux:icon name="newspaper" variant="micro" />
                        <span class="truncate">{{ $media->model->title }}</span>
                    </flux:link>
                @else
                    <flux:text size="sm" class="mt-1">{{ __('Not attached to a page') }}</flux:text>
                @endif
            @endif
        </div>

        <div class="mt-auto flex items-center gap-1">
            <flux:button
                size="sm"
                icon="clipboard-document"
                class="flex-1"
                x-data
                x-on:click="navigator.clipboard.writeText({{ Js::from($media->getUrl()) }}); $flux.toast({{ Js::from(__('URL copied.')) }})"
            >
                {{ __('Copy URL') }}
            </flux:button>

            <flux:button
                size="sm"
                variant="ghost"
                icon="trash"
                wire:click="deleteMedia({{ $media->id }})"
                wire:confirm="{{ __('Delete this file? Bricks that use it will show a broken image.') }}"
                :aria-label="__('Delete')"
            />
        </div>
    </div>
</flux:card>
