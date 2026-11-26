<?php

namespace App\FormFields;

class PhoneField extends InputFormField
{
    public function key(): string
    {
        return 'phone';
    }

    public function label(): string
    {
        return __('Phone');
    }

    public function icon(): string
    {
        return 'phone';
    }

    public function order(): int
    {
        return 30;
    }

    public function inputType(): string
    {
        return 'tel';
    }

    protected function valueRules(array $settings): array
    {
        return ['string', 'max:50', 'regex:/^[0-9+\\-\\s().]+$/'];
    }
}
