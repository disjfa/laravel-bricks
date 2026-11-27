<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Forms') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Build forms and place them on a page with the form brick.') }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search forms...')" class="max-w-xs" clearable />

            @can('create', App\Models\Form::class)
                <flux:modal.trigger name="create-form">
                    <flux:button variant="primary" icon="plus" data-test="new-form-button">{{ __('New form') }}</flux:button>
                </flux:modal.trigger>
            @endcan
        </div>
    </div>

    <flux:table :paginate="$this->forms">
        <flux:table.columns>
            <x-admin.sortable-column column="name" :direction="$this->sortDirection('name')">{{ __('Name') }}</x-admin.sortable-column>
            <flux:table.column>{{ __('Fields') }}</flux:table.column>
            <x-admin.sortable-column column="submissions" :direction="$this->sortDirection('submissions')">{{ __('Submissions') }}</x-admin.sortable-column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->forms as $form)
                <flux:table.row :key="$form->id">
                    <flux:table.cell variant="strong">
                        <a href="{{ route('admin.forms.edit', $form) }}" wire:navigate class="hover:underline">{{ $form->name }}</a>
                    </flux:table.cell>

                    <flux:table.cell>{{ $form->fields_count }}</flux:table.cell>

                    <flux:table.cell>
                        <flux:link :href="route('admin.forms.submissions', $form)" wire:navigate>{{ $form->submissions_count }}</flux:link>
                    </flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-1">
                            @can('update', $form)
                                <flux:button size="sm" variant="ghost" icon="inbox" :href="route('admin.forms.submissions', $form)" wire:navigate inset="top bottom" :aria-label="__('Submissions')" />
                                <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.forms.edit', $form)" wire:navigate inset="top bottom" :aria-label="__('Edit')" />
                            @endcan

                            @can('delete', $form)
                                <flux:modal.trigger :name="'delete-form-'.$form->id">
                                    <flux:button size="sm" variant="ghost" icon="trash" inset="top bottom" :aria-label="__('Delete')" />
                                </flux:modal.trigger>

                                <flux:modal :name="'delete-form-'.$form->id" class="max-w-md">
                                    <div class="space-y-6 text-start">
                                        <div>
                                            <flux:heading size="lg">{{ __('Delete form?') }}</flux:heading>
                                            <flux:text class="mt-2">{{ __('":name", its fields and all of its submissions will be removed.', ['name' => $form->name]) }}</flux:text>
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>

                                            <flux:button variant="danger" wire:click="deleteForm({{ $form->id }})">{{ __('Delete') }}</flux:button>
                                        </div>
                                    </div>
                                </flux:modal>
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="py-8 text-center">{{ __('No forms yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="create-form" :show="$errors->has('name')" class="max-w-lg">
        <form wire:submit="createForm" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('New form') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Give your form a name, you can add fields next.') }}</flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" required autofocus />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">{{ __('Create form') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>