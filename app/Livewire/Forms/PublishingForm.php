<?php

namespace App\Livewire\Forms;

use App\Concerns\PublishedTrait;
use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * The publishing fields of a model that uses PublishedTrait, shared by the page and blog editors.
 * Render it with <x-admin.publishing-fields />.
 */
class PublishingForm extends Form
{
    /**
     * The format of the date fields, the date picker writes the same format.
     */
    public const string DateFormat = 'Y-m-d H:i';

    public string $status = 'concept';

    public string $publishedAt = '';

    public string $publishedUntil = '';

    public int|string $seqnr = 0;

    /**
     * @param  Model&PublishedTrait  $model
     */
    public function fillFromModel(Model $model): void
    {
        $this->status = ($model->status ?? PublishStatus::Concept)->value;
        $this->publishedAt = $model->published_at?->format(self::DateFormat) ?? '';
        $this->publishedUntil = $model->published_until?->format(self::DateFormat) ?? '';
        $this->seqnr = $model->seqnr ?? 0;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(PublishStatus::class)],
            'publishedAt' => ['nullable', 'date'],
            'publishedUntil' => ['nullable', 'date', ...(filled($this->publishedAt) ? ['after:'.$this->publishedAt] : [])],
            'seqnr' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'publishedAt' => __('publish date'),
            'publishedUntil' => __('publish until date'),
            'seqnr' => __('sequence number'),
        ];
    }

    /**
     * The validated values as model attributes.
     *
     * @return array{status: PublishStatus, published_at: Carbon|null, published_until: Carbon|null, seqnr: int}
     */
    public function toAttributes(): array
    {
        $this->validate();

        return [
            'status' => PublishStatus::from($this->status),
            'published_at' => filled($this->publishedAt) ? Carbon::parse($this->publishedAt) : null,
            'published_until' => filled($this->publishedUntil) ? Carbon::parse($this->publishedUntil) : null,
            'seqnr' => (int) $this->seqnr,
        ];
    }
}
