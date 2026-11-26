<?php

namespace App\FormFields;

class TextField extends InputFormField
{
    public function key(): string
    {
        return 'text';
    }

    public function label(): string
    {
        return __('Text');
    }

    public function icon(): string
    {
        return 'pencil';
    }

    public function order(): int
    {
        return 10;
    }

    public function inputType(): string
    {
        return 'text';
    }

    protected function valueRules(array $settings): array
    {
        return ['string', 'max:255'];
    }
}
