<?php

namespace App\FormFields;

class NumberField extends InputFormField
{
    public function key(): string
    {
        return 'number';
    }

    public function label(): string
    {
        return __('Number');
    }

    public function icon(): string
    {
        return 'hashtag';
    }

    public function order(): int
    {
        return 40;
    }

    public function inputType(): string
    {
        return 'number';
    }

    protected function valueRules(array $settings): array
    {
        return ['numeric'];
    }
}
