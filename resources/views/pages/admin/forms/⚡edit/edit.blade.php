{{-- Like the brick editor: the fields on the left, the live preview on the right. --}}
<x-admin.form-screen :form="$form" current="fields" save="save" columns>
    {{-- Canvas --}}
    <x-admin.screen.column class="space-y-2">
        <div wire:sort="sortField" class="space-y-2">
            @foreach ($fields as $index => $field)
                @php($type = $this->fieldType($field['type']))

                {{-- The key includes the position: the inputs bind to their position (like fields.0.label), so a moved field is rendered fresh with the right bindings. --}}
                <div wire:key="field-{{ $field['key'] }}-{{ $index }}" wire:sort:item="{{ $field['key'] }}" class="group/field">
                    <flux:card class="space-y-4 p-4! transition group-hover/field:ring-2 group-hover/field:ring-zinc-200 dark:group-hover/field:ring-zinc-600">
                        <div class="flex items-center gap-2">
                            <div wire:sort:handle class="cursor-grab text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" title="{{ __('Drag to reorder') }}">
                                <flux:icon name="bars-2" variant="micro" />
                            </div>

                            <flux:icon :name="$type->icon()" variant="micro" class="text-zinc-500" />
                            <flux:text size="sm" class="font-medium">{{ $type->label() }}</flux:text>

                            <flux:spacer />

                            <div wire:sort:ignore class="flex items-center gap-0.5 opacity-60 transition group-hover/field:opacity-100">
                                <flux:button size="xs" variant="ghost" icon="chevron-up" wire:click="moveField('{{ $field['key'] }}', -1)" :disabled="$loop->first" :aria-label="__('Move up')" />
                                <flux:button size="xs" variant="ghost" icon="chevron-down" wire:click="moveField('{{ $field['key'] }}', 1)" :disabled="$loop->last" :aria-label="__('Move down')" />
                                <flux:button size="xs" variant="ghost" icon="document-duplicate" wire:click="duplicateField('{{ $field['key'] }}')" :aria-label="__('Duplicate')" />
                                <flux:button size="xs" variant="ghost" icon="trash" wire:click="removeField('{{ $field['key'] }}')" :aria-label="__('Remove')" />
                            </div>
                        </div>

                        <div wire:sort:ignore class="grid gap-4 sm:grid-cols-2">
                            @foreach ($type->settings() as $setting)
                                <div @class(['sm:col-span-2' => ! $setting->isHalfWidth()]) wire:key="setting-{{ $field['key'] }}-{{ $setting->name }}">
                                    @include($setting->view(), [
                                        'field' => $setting,
                                        'path' => "fields.{$index}.settings.{$setting->name}",
                                        'value' => $field['settings'][$setting->name] ?? null,
                                        'imageMedia' => [],
                                        'mediaUrl' => null,
                                    ])
                                </div>
                            @endforeach

                            <div class="sm:col-span-2">
                                <flux:input wire:model="fields.{{ $index }}.name" size="sm" :label="__('Key')" :placeholder="__('Generated from the label')" :description:trailing="__('Stores the answer in the submissions.')" class:input="font-mono" />
                            </div>
                        </div>
                    </flux:card>

                    {{-- Inserter between fields --}}
                    <div wire:sort:ignore class="flex h-6 items-center justify-center opacity-0 transition group-hover/field:opacity-100 focus-within:opacity-100">
                        @include('form-fields.inserter', ['position' => $index + 1, 'compact' => true])
                    </div>
                </div>
            @endforeach
        </div>

        @if ($fields === [])
            <div class="rounded-xl border-2 border-dashed border-zinc-200 px-6 py-16 text-center dark:border-zinc-700">
                <flux:icon name="queue-list" class="mx-auto size-10 text-zinc-400" />
                <flux:heading class="mt-4">{{ __('This form has no fields yet') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Add your first field to start building.') }}</flux:text>
            </div>
        @endif

        {{-- Sticky at the bottom of the canvas column, so adding is always one click away. --}}
        <div class="sticky bottom-0 z-10 -mx-4 -mb-4 flex justify-center bg-linear-to-t from-white from-60% to-transparent px-4 pt-8 pb-4 dark:from-zinc-800">
            @include('form-fields.inserter', ['position' => null, 'compact' => false])
        </div>
    </x-admin.screen.column>

    {{-- Live preview, rendered with the website field views --}}
    <x-admin.screen.column divided data-test="form-preview">
        <div class="pointer-events-none sticky top-4 z-10 flex h-0 justify-end">
            <div class="me-3 -mt-3 flex items-center gap-1.5 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-zinc-600 shadow-sm ring-1 ring-zinc-200 backdrop-blur dark:bg-zinc-800/90 dark:text-zinc-300 dark:ring-zinc-700" title="{{ __('Unsaved changes included') }}">
                <span class="size-1.5 animate-pulse rounded-full bg-green-500"></span>
                {{ __('Live preview') }}
            </div>
        </div>

        <livewire:form-preview :fields="$fields" :submit-label="$form->submitLabel()" :description="$form->description ?? ''" wire:key="form-preview" />
    </x-admin.screen.column>
</x-admin.form-screen>