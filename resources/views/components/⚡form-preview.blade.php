<?php

use App\FormFields\FormFieldRegistry;
use App\FormFields\FormFieldType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Renders form fields with the website views. Listens for "form-fields-updated", which the form
 * builder dispatches with its unsaved fields whenever they change.
 */
new class extends Component {
    /**
     * @var array<int, array{key: string, type: string, settings: array<string, mixed>}>
     */
    public array $fields = [];

    public string $submitLabel = '';

    public string $description = '';

    /**
     * Lets editors try the fields in the preview, nothing is submitted.
     *
     * @var array<string, mixed>
     */
    public array $values = [];

    /**
     * @param  array<int, array{key: string, type: string, settings: array<string, mixed>}>  $fields
     */
    #[On('form-fields-updated')]
    public function updateFields(array $fields, string $submitLabel = '', string $description = ''): void
    {
        $this->fields = $fields;
        $this->submitLabel = $submitLabel;
        $this->description = $description;
    }

    /**
     * Fields that are not valid yet have no settings and render as a placeholder.
     *
     * @return array<int, array{key: string, type: FormFieldType, settings: array<string, mixed>|null}>
     */
    #[Computed]
    public function renderableFields(): array
    {
        $registry = app(FormFieldRegistry::class);

        return collect($this->fields)
            ->filter(fn (array $field) => $registry->find($field['type'] ?? '') !== null)
            ->map(function (array $field) use ($registry): array {
                $type = $registry->find($field['type']);
                $validator = $type->validator($field['settings'] ?? []);

                return [
                    'key' => (string) ($field['key'] ?? ''),
                    'type' => $type,
                    'settings' => $validator->fails() ? null : $validator->validated(),
                ];
            })
            ->values()
            ->all();
    }
}; ?>

<div class="overflow-x-hidden rounded-xl border lg:min-h-full border-zinc-200 antialiased dark:border-zinc-700" data-site-theme>
    @include('partials.site-theme')

    <div class="mx-auto max-w-3xl space-y-6 px-6 py-12">
        @if (filled($description))
            <p class="text-lg text-zinc-700 dark:text-zinc-300">{{ $description }}</p>
        @endif

        @forelse ($this->renderableFields as $field)
            <div wire:key="preview-{{ $field['key'] }}">
                @if ($field['settings'] === null)
                    <div class="flex items-center gap-3 rounded-xl border-2 border-dashed border-zinc-300 px-4 py-6 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                        <flux:icon :name="$field['type']->icon()" variant="mini" />
                        {{ __(':type: fill in the required settings to preview this field.', ['type' => $field['type']->label()]) }}
                    </div>
                @else
                    @include($field['type']->view(), [
                        'type' => $field['type'],
                        'settings' => $field['settings'],
                        'model' => 'values.'.$field['key'],
                    ])
                @endif
            </div>
        @empty
            <flux:text class="text-center">{{ __('Add fields to see them here.') }}</flux:text>
        @endforelse

        @if ($this->renderableFields !== [])
            <flux:button variant="primary" disabled>{{ filled($submitLabel) ? $submitLabel : __('Send') }}</flux:button>
        @endif
    </div>
</div>
