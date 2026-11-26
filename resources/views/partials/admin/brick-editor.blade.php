{{--
    The brick editor of a page or blog, used together with App\Livewire\Concerns\EditsBricks.
    Receives $model (the Brickable), $bricks and $imageMedia.

    An <x-admin.screen> with two columns: the bricks on the left and the live preview on the right.
--}}
@php
    $registry = app(App\Bricks\BrickRegistry::class);
    $mediaUrl = route("admin.{$model->adminSection()}.media", $model);
@endphp

<x-admin.content-screen :model="$model" current="content" save="save" columns>
    {{-- Canvas --}}
    <x-admin.screen.column class="space-y-2">
        <div wire:sort="sortBrick" class="space-y-2">
            @foreach ($bricks as $index => $brick)
                @php($type = $registry->find($brick['type']))

                <div wire:key="brick-{{ $brick['key'] }}" wire:sort:item="{{ $brick['key'] }}" class="group/brick">
                    <flux:card class="space-y-4 p-4! transition group-hover/brick:ring-2 group-hover/brick:ring-zinc-200 dark:group-hover/brick:ring-zinc-600">
                        <div class="flex items-center gap-2">
                            <div wire:sort:handle class="cursor-grab text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" title="{{ __('Drag to reorder') }}">
                                <flux:icon name="bars-2" variant="micro" />
                            </div>

                            <flux:icon :name="$type->icon()" variant="micro" class="text-zinc-500" />
                            <flux:text size="sm" class="font-medium">{{ $type->label() }}</flux:text>

                            @if (($width = App\Enums\BrickWidth::fromSettings($brick['settings'] ?? [])) !== App\Enums\BrickWidth::Normal)
                                <flux:badge size="sm" color="zinc">{{ $width->label() }}</flux:badge>
                            @endif

                            @if ($errors->has("bricks.{$index}.settings.*"))
                                <flux:badge size="sm" color="red">{{ __('Check the settings') }}</flux:badge>
                            @endif

                            <flux:spacer />

                            <div wire:sort:ignore class="flex items-center gap-0.5 opacity-60 transition group-hover/brick:opacity-100">
                                <flux:button size="xs" variant="ghost" icon="chevron-up" wire:click="moveBrick('{{ $brick['key'] }}', -1)" :disabled="$loop->first" :aria-label="__('Move up')" />
                                <flux:button size="xs" variant="ghost" icon="chevron-down" wire:click="moveBrick('{{ $brick['key'] }}', 1)" :disabled="$loop->last" :aria-label="__('Move down')" />
                                <flux:modal.trigger :name="'brick-settings-'.$brick['key']">
                                    <flux:button size="xs" variant="ghost" icon="cog-6-tooth" :aria-label="__('Brick settings')" data-test="brick-settings-button" />
                                </flux:modal.trigger>
                                <flux:button size="xs" variant="ghost" icon="document-duplicate" wire:click="duplicateBrick('{{ $brick['key'] }}')" :aria-label="__('Duplicate')" />
                                <flux:button size="xs" variant="ghost" icon="trash" wire:click="removeBrick('{{ $brick['key'] }}')" :aria-label="__('Remove')" />
                            </div>
                        </div>

                        <div wire:sort:ignore class="grid gap-4 sm:grid-cols-2">
                            @forelse ($type->fields() as $field)
                                <div @class(['sm:col-span-2' => ! $field->isHalfWidth()]) wire:key="field-{{ $brick['key'] }}-{{ $field->name }}">
                                    @include($field->view(), [
                                        'field' => $field,
                                        'path' => "bricks.{$index}.data.{$field->name}",
                                        'value' => $brick['data'][$field->name] ?? null,
                                        'imageMedia' => $imageMedia,
                                        'mediaUrl' => $mediaUrl,
                                    ])
                                </div>
                            @empty
                                <flux:text size="sm" class="sm:col-span-2">{{ __('This brick has no settings.') }}</flux:text>
                            @endforelse
                        </div>
                    </flux:card>

                    {{-- Settings that every brick has, like its width --}}
                    <flux:modal :name="'brick-settings-'.$brick['key']" class="w-full max-w-lg" wire:sort:ignore>
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __(':type settings', ['type' => $type->label()]) }}</flux:heading>
                                <flux:text class="mt-2">{{ __('Changes show in the preview right away and are stored when you save.') }}</flux:text>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach ($type->settings() as $setting)
                                    <div @class(['sm:col-span-2' => ! $setting->isHalfWidth()]) wire:key="setting-{{ $brick['key'] }}-{{ $setting->name }}">
                                        @include($setting->view(), [
                                            'field' => $setting,
                                            'path' => "bricks.{$index}.settings.{$setting->name}",
                                            'value' => $brick['settings'][$setting->name] ?? null,
                                            'imageMedia' => $imageMedia,
                                            'mediaUrl' => $mediaUrl,
                                        ])
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex justify-end">
                                <flux:modal.close>
                                    <flux:button variant="primary">{{ __('Done') }}</flux:button>
                                </flux:modal.close>
                            </div>
                        </div>
                    </flux:modal>

                    {{-- Inserter between bricks --}}
                    <div wire:sort:ignore class="flex h-6 items-center justify-center opacity-0 transition group-hover/brick:opacity-100 focus-within:opacity-100">
                        @include('bricks.editor.inserter', ['position' => $index + 1, 'compact' => true])
                    </div>
                </div>
            @endforeach
        </div>

        @if ($bricks === [])
            <div class="rounded-xl border-2 border-dashed border-zinc-200 px-6 py-16 text-center dark:border-zinc-700">
                <flux:icon name="squares-plus" class="mx-auto size-10 text-zinc-400" />
                <flux:heading class="mt-4">{{ __('There are no bricks yet') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Add your first brick to start building.') }}</flux:text>
            </div>
        @endif

        {{-- Sticky at the bottom of the canvas column, so adding is always one click away. --}}
        <div class="sticky bottom-0 z-10 -mx-4 -mb-4 flex justify-center bg-linear-to-t from-white from-60% to-transparent px-4 pt-8 pb-4 dark:from-zinc-800">
            @include('bricks.editor.inserter', ['position' => null, 'compact' => false])
        </div>
    </x-admin.screen.column>

    {{-- Live preview, rendered with the website brick views --}}
    {{-- The column itself scrolls (like the canvas), so both scrollbars run the full height. --}}
    <x-admin.screen.column divided data-test="page-preview">
        {{-- Zero-height sticky row: the badge sits on the card's top edge and stays pinned while scrolling. --}}
        <div class="pointer-events-none sticky top-4 z-10 flex h-0 justify-end">
            <div class="me-3 -mt-3 flex items-center gap-1.5 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-zinc-600 shadow-sm ring-1 ring-zinc-200 backdrop-blur dark:bg-zinc-800/90 dark:text-zinc-300 dark:ring-zinc-700" title="{{ __('Unsaved changes included') }}">
                <span class="size-1.5 animate-pulse rounded-full bg-green-500"></span>
                {{ __('Live preview') }}
            </div>
        </div>

        <livewire:brick-preview :bricks="$bricks" wire:key="brick-preview" />
    </x-admin.screen.column>
</x-admin.content-screen>
