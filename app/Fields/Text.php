<?php

namespace App\Fields;

class Text extends Field
{
    protected function typeRules(): array
    {
        return ['string', 'max:255'];
    }

    public function view(): string
    {
        return 'fields.text';
    }
}
