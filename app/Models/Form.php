<?php

namespace App\Models;

use Database\Factories\FormFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A form built in the form builder, placed on pages with the form brick.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $submit_label
 * @property string|null $success_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, FormField> $fields
 * @property-read Collection<int, FormSubmission> $submissions
 */
#[Fillable(['name', 'description', 'submit_label', 'success_message'])]
class Form extends Model
{
    /** @use HasFactory<FormFactory> */
    use HasFactory;

    /**
     * @return HasMany<FormField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('position');
    }

    /**
     * @return HasMany<FormSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function submitLabel(): string
    {
        return filled($this->submit_label) ? $this->submit_label : __('Send');
    }

    public function successMessage(): string
    {
        return filled($this->success_message) ? $this->success_message : __('Thank you, we received your message.');
    }

    /**
     * Validation rules for a submission, keyed by field name.
     *
     * @return array<string, array<int, mixed>>
     */
    public function submissionRules(): array
    {
        return $this->renderableFields()
            ->mapWithKeys(fn (FormField $field) => [$field->name => $field->rules()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function submissionAttributes(): array
    {
        return $this->renderableFields()
            ->mapWithKeys(fn (FormField $field) => [$field->name => mb_strtolower($field->label())])
            ->all();
    }

    /**
     * The fields whose type still exists.
     *
     * @return Collection<int, FormField>
     */
    public function renderableFields(): Collection
    {
        return $this->fields
            ->filter(fn (FormField $field) => $field->definition() !== null)
            ->values();
    }
}
