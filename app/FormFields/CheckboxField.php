<?php

namespace App\FormFields;

class CheckboxField extends BaseFormField
{
    public function key(): string
    {
        return 'checkbox';
    }

    public function label(): string
    {
        return __('Checkbox');
    }

    public function icon(): string
    {
        return 'check-circle';
    }

    public function order(): int
    {
        return 80;
    }

    /**
     * A required checkbox has to be ticked, like accepting the terms.
     */
    public function rules(array $settings): array
    {
        return [
            ($settings['required'] ?? false) ? 'accepted' : 'nullable',
            'boolean',
        ];
    }

    protected function valueRules(array $settings): array
    {
        return ['boolean'];
    }

    public function emptyValue(): mixed
    {
        return false;
    }
}
