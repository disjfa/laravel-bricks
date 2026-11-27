<?php

namespace App\Bricks;

use App\Features\Forms;
use App\Fields\Select;
use App\Models\Form;
use Laravel\Pennant\Feature;

/**
 * A form from the form builder, rendered by a Livewire component that stores the submissions.
 */
class FormBrick extends BaseBrick
{
    public function key(): string
    {
        return 'form';
    }

    public function label(): string
    {
        return __('Form');
    }

    public function icon(): string
    {
        return 'queue-list';
    }

    public function order(): int
    {
        return 90;
    }

    public function isAvailable(): bool
    {
        return Feature::active(Forms::class);
    }

    public function fields(): array
    {
        return [
            Select::make('form_id')
                ->label(__('Form'))
                ->required()
                ->help(__('Build forms in the admin under Forms.'))
                ->options(Form::query()->orderBy('name')->pluck('name', 'id')->all()),
        ];
    }
}
