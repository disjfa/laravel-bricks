{{--
    EasyMDE editor (resources/js/markdown-editor.js). Livewire leaves the editor alone (wire:ignore);
    data-path is kept up to date by Livewire, so a reordered brick still writes to the right field.
    The image picker below the label is rendered by Livewire, so new uploads show up without a reload.
--}}
<flux:field x-data="markdownEditor" data-path="{{ $path }}" data-test="markdown-editor">
    <flux:label>{{ $field->getLabel() }}</flux:label>

    <div x-show="imagePickerOpen" x-cloak x-transition class="mb-2 rounded-lg border border-zinc-200 bg-zinc-50 p-2 dark:border-white/10 dark:bg-white/5" data-test="markdown-image-picker">
        <div class="mb-2 flex items-center justify-between">
            <flux:text size="sm" class="font-medium">{{ __('Insert an image from the media library') }}</flux:text>
            <flux:button size="xs" variant="ghost" icon="x-mark" x-on:click="imagePickerOpen = false" :aria-label="__('Close')" />
        </div>

        @if ($imageMedia === [])
            <flux:text size="sm">
                {{ __('This page has no images yet.') }}
                <flux:link :href="route('admin.pages.media', $page)" wire:navigate>{{ __('Upload images in the media panel') }}</flux:link>
            </flux:text>
        @else
            <div class="grid max-h-56 grid-cols-3 gap-2 overflow-y-auto sm:grid-cols-4">
                @foreach ($imageMedia as $item)
                    <button
                        type="button"
                        wire:key="markdown-image-{{ $path }}-{{ $item['id'] }}"
                        x-on:click="insertImage({{ Js::from($item['url']) }}, {{ Js::from($item['name']) }})"
                        class="group overflow-hidden rounded-md text-start ring-1 ring-zinc-200 hover:ring-2 hover:ring-zinc-500 dark:ring-white/10 dark:hover:ring-white"
                        title="{{ $item['name'] }}"
                    >
                        <img src="{{ $item['thumb'] }}" alt="" class="aspect-4/3 w-full object-cover" loading="lazy">
                        <span class="block truncate px-1.5 py-1 text-xs text-zinc-600 dark:text-zinc-300">{{ $item['name'] }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <div wire:ignore>
        <textarea x-ref="input" class="hidden">{{ $value }}</textarea>
    </div>

    @if (filled($field->getHelp()))
        <flux:description>{{ $field->getHelp() }}</flux:description>
    @endif

    <flux:error :name="$path" />
</flux:field>
