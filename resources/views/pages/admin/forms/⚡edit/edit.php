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
use Illuminate\View\View;
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

    public function render(): View
    {
        return $this->view()->title(__('Edit :name', ['name' => $this->form->name]));
    }
}; 