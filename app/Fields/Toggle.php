<?php

namespace App\Fields;

/**
 * An on/off setting.
 */
class Toggle extends Field
{
    protected mixed $default = false;

    public function getRules(): array
    {
        return ['boolean', ...$this->extraRules];
    }

    protected function typeRules(): array
    {
        return ['boolean'];
    }

    public function view(): string
    {
        return 'fields.toggle';
    }
}
