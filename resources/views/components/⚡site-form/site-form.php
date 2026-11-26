<?php

use App\Features\Forms;
use App\Models\Form;
use App\Models\FormField;
use App\Rules\Turnstile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * A form from the form builder on the website, rendered by the form brick.
 *
 * Spam protection: Cloudflare Turnstile when it is configured (config/services.php), otherwise a honeypot
 * field and a minimum fill time. Submissions are also limited per visitor.
 */
new class extends Component
{
    /**
     * Without Turnstile, submissions that come in faster than this are refused, people need longer to fill in a form.
     */
    public const int MinimumFillSeconds = 2;

    #[Locked]
    public int $formId;

    /**
     * When the form was shown, for the minimum fill time.
     */
    #[Locked]
    public int $renderedAt = 0;

    /**
     * The Cloudflare Turnstile token, set by the widget (resources/js/turnstile.js).
     */
    public string $turnstileToken = '';

    /**
     * The honeypot: a field people never see, so only bots fill it in.
     */
    public string $website = '';

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
        $this->renderedAt = now()->getTimestamp();

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

        if (! $this->passesSpamCheck()) {
            // Act as if it worked, so a bot learns nothing. Nothing is stored.
            $this->resetValues();
            $this->submitted = true;

            return;
        }

        $form->submissions()->create([
            'data' => $form->renderableFields()
                ->mapWithKeys(fn (FormField $field) => [$field->name => $validated[$field->name] ?? $field->emptyValue()])
                ->all(),
            'ip_address' => request()->ip(),
        ]);

        $this->resetValues();
        $this->submitted = true;
    }

    /**
     * Turnstile when it is configured, the honeypot and minimum fill time otherwise.
     *
     * @throws ValidationException when the visitor should try again
     */
    protected function passesSpamCheck(): bool
    {
        if (Turnstile::isEnabled()) {
            try {
                $this->validate(
                    ['turnstileToken' => ['required', new Turnstile(request()->ip())]],
                    ['turnstileToken.required' => __('Please confirm that you are not a robot.')],
                );
            } finally {
                // A token can be checked once, the widget gets a new one for the next attempt.
                $this->turnstileToken = '';
                $this->dispatch('turnstile-reset', id: $this->getId());
            }

            return true;
        }

        if (filled($this->website)) {
            return false;
        }

        if (now()->getTimestamp() - $this->renderedAt < self::MinimumFillSeconds) {
            throw ValidationException::withMessages([
                'form' => __('That was quick! Please check your answers and send the form again.'),
            ]);
        }

        return true;
    }

    protected function resetValues(): void
    {
        $this->values = $this->form()?->renderableFields()
            ->mapWithKeys(fn (FormField $field) => [$field->name => $field->emptyValue()])
            ->all() ?? [];
    }
};
