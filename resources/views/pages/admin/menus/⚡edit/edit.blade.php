<x-admin.screen :title="$menu->name" :subtitle="$menu->handle" :back="route('admin.menus.index')" save="save">
    <form wire:submit="save" class="grid items-start gap-6 lg:grid-cols-3">
        <div class="space-y-2 lg:col-span-2">
            <div wire:sort="sortItem" class="space-y-2">
                @foreach ($items as $index => $item)
                    @php($type = $this->linkType($item['type']))

                    {{-- The key includes the position: the inputs bind to their position (like items.0.label), so a moved item is rendered fresh with the right bindings. --}}
                    <div wire:key="item-{{ $item['key'] }}-{{ $index }}" wire:sort:item="{{ $item['key'] }}" class="group/item">
                        <flux:card class="p-4! transition group-hover/item:ring-2 group-hover/item:ring-zinc-200 dark:group-hover/item:ring-zinc-600">
                            <div class="flex items-start gap-3">
                                <div wire:sort:handle class="mt-1 cursor-grab text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" title="{{ __('Drag to reorder') }}">
                                    <flux:icon name="bars-2" variant="micro" />
                                </div>

                                <div wire:sort:ignore class="flex-1 space-y-4">
                                    <div class="flex items-center gap-2 text-zinc-500">
                                        <flux:icon :name="$type->icon()" variant="micro" />
                                        <flux:text size="sm" class="font-medium">{{ $type->label() }}</flux:text>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <flux:input wire:model="items.{{ $index }}.label" :label="__('Label')" />

                                        @foreach ($type->fields() as $field)
                                            <div @class(['sm:col-span-2' => ! $field->isHalfWidth() && ! $loop->first]) wire:key="field-{{ $item['key'] }}-{{ $field->name }}">
                                                @include($field->view(), [
                                                    'field' => $field,
                                                    'path' => "items.{$index}.data.{$field->name}",
                                                    'value' => $item['data'][$field->name] ?? null,
                                                    'imageMedia' => [],
                                                    'mediaUrl' => null,
                                                ])
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div wire:sort:ignore class="flex items-center gap-0.5 opacity-60 transition group-hover/item:opacity-100">
                                    <flux:button size="xs" variant="ghost" icon="chevron-up" wire:click="moveItem('{{ $item['key'] }}', -1)" :disabled="$loop->first" :aria-label="__('Move up')" />
                                    <flux:button size="xs" variant="ghost" icon="chevron-down" wire:click="moveItem('{{ $item['key'] }}', 1)" :disabled="$loop->last" :aria-label="__('Move down')" />
                                    <flux:button size="xs" variant="ghost" icon="trash" wire:click="removeItem('{{ $item['key'] }}')" :aria-label="__('Remove')" />
                                </div>
                            </div>
                        </flux:card>
                    </div>
                @endforeach
            </div>

            @if ($items === [])
                <div class="rounded-xl border-2 border-dashed border-zinc-200 px-6 py-16 text-center dark:border-zinc-700">
                    <flux:icon name="bars-3" class="mx-auto size-10 text-zinc-400" />
                    <flux:heading class="mt-4">{{ __('This menu has no items yet') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('Add an item to link to a page, a blog or any other address.') }}</flux:text>
                </div>
            @endif

            <div class="flex justify-center pt-2">
                <flux:dropdown position="bottom" align="center">
                    <flux:button icon="plus" data-test="add-menu-item">{{ __('Add item') }}</flux:button>

                    <flux:menu>
                        @foreach (app(App\MenuLinks\MenuLinkRegistry::class)->available() as $linkType)
                            <flux:menu.item :icon="$linkType->icon()" wire:click="addItem('{{ $linkType->key() }}')">{{ $linkType->label() }}</flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        <flux:card class="space-y-6 lg:sticky lg:top-0">
            <flux:heading size="lg">{{ __('Settings') }}</flux:heading>

            <flux:input wire:model="name" :label="__('Name')" required />

            <flux:input wire:model="handle" :label="__('Handle')" :description:trailing="__('Use :main for the header menu or :footer for the footer menu.', ['main' => App\Models\Menu::MainHandle, 'footer' => App\Models\Menu::FooterHandle])" required class:input="font-mono" />

            <flux:text size="sm">{{ __('Items that link to a page or blog that is not published are hidden on the website.') }}</flux:text>
        </flux:card>

        <button type="submit" class="hidden"></button>
    </form>
</x-admin.screen>