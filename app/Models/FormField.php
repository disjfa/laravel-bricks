<?php

namespace App\Models;

use App\FormFields\FormFieldRegistry;
use App\FormFields\FormFieldType;
use Database\Factories\FormFieldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * One field of a form. Like a brick, its type decides which settings it has and how it renders.
 *
 * @property int $id
 * @property int $form_id
 * @property string $type
 * @property string $name
 * @property array<string, mixed> $settings
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Form $form
 */
#[Fillable(['type', 'name', 'settings', 'position'])]
class FormField extends Model
{
    /** @use HasFactory<FormFieldFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'position' => 'integer',
        ];
    }

    /**
     * The field type this field is built from, or null when that type no longer exists.
     */
    public function definition(): ?FormFieldType
    {
        return app(FormFieldRegistry::class)->find($this->type);
    }

    /**
     * @return BelongsTo<Form, $this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function label(): string
    {
        return filled($this->settings['label'] ?? null) ? $this->settings['label'] : Str::headline($this->name);
    }

    /**
     * The rules for the value a visitor submits.
     *
     * @return array<int, mixed>
     */
    public function rules(): array
    {
        return $this->definition()?->rules($this->settings) ?? ['nullable'];
    }

    public function emptyValue(): mixed
    {
        return $this->definition()?->emptyValue() ?? '';
    }
}
