<?php

use App\FormFields\FormFieldRegistry;
use App\FormFields\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use Flux\Flux;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    public Form $form;

    /**
     * The fields being edited, in display order. Saved fields carry their database id.
     * Like bricks, every field type has its own settings.
     *
     * @var array<int, array{key: string, id: int|null, type: string, name: string, settings: array<string, mixed>}>
     */
    public array $fields = [];

    public function mount(Form $form): void
    {
        $this->authorize('update', $form);

        $this->form = $form;

        $this->fields = $form->renderableFields()
            ->map(fn (FormField $field) => [
                'key' => (string) Str::uuid(),
                'id' => $field->id,
                'type' => $field->type,
                'name' => $field->name,
                'settings' => array_merge($field->definition()->defaults(), $field->settings),
            ])
            ->all();
    }

    public function updated(string $property): void
    {
        if (Str::startsWith($property, 'fields.')) {
            $this->dispatchFieldsUpdated();
        }
    }

    public function addField(string $type, ?int $index = null): void
    {
        $fieldType = $this->fieldType($type);

        $field = [
            'key' => (string) Str::uuid(),
            'id' => null,
            'type' => $fieldType->key(),
            'name' => '',
            'settings' => $fieldType->defaults(),
        ];

        $index ??= count($this->fields);

        array_splice($this->fields, $index, 0, [$field]);

        $this->dispatchFieldsUpdated();
    }

    public function duplicateField(string $key): void
    {
        $index = $this->indexOf($key);

        $copy = [
            ...$this->fields[$index],
            'key' => (string) Str::uuid(),
            'id' => null,
            'name' => '',
        ];

        array_splice($this->fields, $index + 1, 0, [$copy]);

        $this->dispatchFieldsUpdated();
    }

    public function removeField(string $key): void
    {
        array_splice($this->fields, $this->indexOf($key), 1);

        $this->resetValidation();
        $this->dispatchFieldsUpdated();
    }

    public function moveField(string $key, int $direction): void
    {
        $this->sortField($key, $this->indexOf($key) + $direction);
    }

    /**
     * Handler for wire:sort.
     */
    public function sortField(string $key, int $position): void
    {
        $index = $this->indexOf($key);
        $position = max(0, min($position, count($this->fields) - 1));

        $field = array_splice($this->fields, $index, 1);
        array_splice($this->fields, $position, 0, $field);

        $this->resetValidation();
        $this->dispatchFieldsUpdated();
    }

    public function save(): void
    {
        $this->authorize('update', $this->form);

        $this->fillMissingFieldNames();

        $this->validate([
            'fields' => ['array', 'max:50'],
            'fields.*.type' => ['required', Rule::in(app(FormFieldRegistry::class)->keys())],
            'fields.*.name' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_]*$/', 'distinct'],
        ], [
            'fields.*.name.regex' => __('The key may only contain lowercase letters, numbers and underscores, and must start with a letter.'),
            'fields.*.name.distinct' => __('Every field needs its own key.'),
        ], [
            'fields.*.name' => __('key'),
        ]);

        $fields = $this->validatedFields();

        DB::transaction(function () use ($fields) {
            $this->form->fields()
                ->whereNotIn('id', array_filter(Arr::pluck($fields, 'id')))
                ->delete();

            // Free the keys first, so fields can swap keys without hitting the unique index.
            foreach ($this->form->fields()->pluck('id') as $fieldId) {
                $this->form->fields()->whereKey($fieldId)->update(['name' => "__{$fieldId}"]);
            }

            foreach ($fields as $position => $field) {
                $attributes = [
                    'type' => $field['type'],
                    'name' => $field['name'],
                    'settings' => $field['settings'],
                    'position' => $position,
                ];

                $saved = $field['id']
                    ? tap($this->form->fields()->findOrFail($field['id']))->update($attributes)
                    : $this->form->fields()->create($attributes);

                $this->fields[$position]['id'] = $saved->id;
            }

            $this->form->touch();
        });

        Flux::toast(variant: 'success', text: __('Form saved.'));
    }

    /**
     * Let the live preview know the unsaved fields changed.
     */
    protected function dispatchFieldsUpdated(): void
    {
        $this->dispatch('form-fields-updated', fields: $this->fields, submitLabel: $this->form->submitLabel(), description: $this->form->description ?? '');
    }

    /**
     * Validate the settings of every field against its type and keep only the known settings.
     *
     * @return array<int, array{key: string, id: int|null, type: string, name: string, settings: array<string, mixed>}>
     */
    protected function validatedFields(): array
    {
        $errors = [];
        $fields = [];

        foreach ($this->fields as $index => $field) {
            $validator = $this->fieldType($field['type'])->validator($field['settings']);

            foreach ($validator->errors()->messages() as $setting => $messages) {
                $errors["fields.{$index}.settings.{$setting}"] = $messages;
            }

            $fields[] = [...$field, 'settings' => $validator->errors()->isEmpty() ? $validator->validated() : []];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $fields;
    }

    /**
     * New fields get a key from their label, like "Your name" becomes "your_name".
     */
    protected function fillMissingFieldNames(): void
    {
        $taken = array_filter(Arr::pluck($this->fields, 'name'));

        foreach ($this->fields as $index => $field) {
            if (filled($field['name'])) {
                continue;
            }

            $base = Str::of((string) ($field['settings']['label'] ?? ''))
                ->ascii()
                ->snake()
                ->replaceMatches('/[^a-z0-9_]/', '')
                ->ltrim('_0123456789')
                ->limit(50, '')
                ->value() ?: 'field';

            $name = $base;
            $suffix = 2;

            while (in_array($name, $taken, true)) {
                $name = "{$base}_{$suffix}";
                $suffix++;
            }

            $taken[] = $name;
            $this->fields[$index]['name'] = $name;
        }
    }

    protected function fieldType(string $key): FormFieldType
    {
        $fieldType = app(FormFieldRegistry::class)->find($key);

        abort_if($fieldType === null, 404);

        return $fieldType;
    }

    protected function indexOf(string $key): int
    {
        $index = collect($this->fields)->search(fn (array $field) => $field['key'] === $key);

        abort_if($index === false, 404);

        return $index;
    }

    public function render()
    {
        return $this->view()->title(__('Edit :name', ['name' => $this->form->name]));
    }
}; ?>

{{-- Like the brick editor: the fields on the left, the live preview on the right. --}}
<x-admin.form-screen :form="$form" current="fields" save="save" columns>
    {{-- Canvas --}}
    <x-admin.screen.column class="space-y-2">
        <div wire:sort="sortField" class="space-y-2">
            @foreach ($fields as $index => $field)
                @php($type = $this->fieldType($field['type']))

                <div wire:key="field-{{ $field['key'] }}" wire:sort:item="{{ $field['key'] }}" class="group/field">
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
