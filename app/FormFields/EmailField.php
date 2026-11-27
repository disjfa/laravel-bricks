<?php

namespace App\FormFields;

class EmailField extends InputFormField
{
    public function key(): string
    {
        return 'email';
    }

    public function label(): string
    {
        return __('Email');
    }

    public function icon(): string
    {
        return 'at-symbol';
    }

    public function order(): int
    {
        return 20;
    }

    public function inputType(): string
    {
        return 'email';
    }

    protected function valueRules(array $settings): array
    {
        return ['string', 'email', 'max:255'];
    }
}
