<?php

namespace App\FormFields;

use App\Fields\Text;

class TextareaField extends BaseFormField
{
    public function key(): string
    {
        return 'textarea';
    }

    public function label(): string
    {
        return __('Text area');
    }

    public function icon(): string
    {
        return 'bars-3-bottom-left';
    }

    public function order(): int
    {
        return 60;
    }

    protected function extraSettings(): array
    {
        return [
            Text::make('placeholder')->label(__('Placeholder'))->half(),
        ];
    }

    protected function valueRules(array $settings): array
    {
        return ['string', 'max:5000'];
    }
}
