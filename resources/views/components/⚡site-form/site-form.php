<?php

use App\Features\Forms;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * A form from the form builder on the website, rendered by the form brick.
 */
new class extends Component
{
    #[Locked]
    public int $formId;

    /**
     * The answers, keyed by field name.
     *
     * @var array<string, string|bool>
     */
    public array $values = [];

    public bool $submitted = false;

    public function mount(int $formId): void
    {
        $this->formId = $formId;

        $this->resetValues();
    }

    /**
     * The form, or null when it was deleted or forms are turned off.
     */
    #[Computed]
    public function form(): ?Form
    {
        if (Feature::inactive(Forms::class)) {
            return null;
        }

        return Form::query()->with('fields')->find($this->formId);
    }

    public function submit(): void
    {
        $form = $this->form();

        abort_if($form === null, 404);

        $throttleKey = "form-submission:{$form->id}:".request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'form' => __('Too many submissions. Please try again in :seconds seconds.', ['seconds' => RateLimiter::availableIn($throttleKey)]),
            ]);
        }

        $rules = collect($form->submissionRules())->mapWithKeys(fn (array $rules, string $name) => ["values.{$name}" => $rules])->all();
        $attributes = collect($form->submissionAttributes())->mapWithKeys(fn (string $label, string $name) => ["values.{$name}" => $label])->all();

        $validated = $this->validate($rules, attributes: $attributes)['values'] ?? [];

        RateLimiter::hit($throttleKey, 60);

        $form->submissions()->create([
            'data' => $form->renderableFields()
                ->mapWithKeys(fn (FormField $field) => [$field->name => $validated[$field->name] ?? $field->emptyValue()])
                ->all(),
            'ip_address' => request()->ip(),
        ]);

        $this->resetValues();
        $this->submitted = true;
    }

    protected function resetValues(): void
    {
        $this->values = $this->form()?->renderableFields()
            ->mapWithKeys(fn (FormField $field) => [$field->name => $field->emptyValue()])
            ->all() ?? [];
    }
};
