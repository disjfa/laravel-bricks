<?php

namespace App\FormFields;

class DateField extends InputFormField
{
    public function key(): string
    {
        return 'date';
    }

    public function label(): string
    {
        return __('Date');
    }

    public function icon(): string
    {
        return 'calendar';
    }

    public function order(): int
    {
        return 50;
    }

    public function inputType(): string
    {
        return 'date';
    }

    protected function valueRules(array $settings): array
    {
        return ['date'];
    }
}
