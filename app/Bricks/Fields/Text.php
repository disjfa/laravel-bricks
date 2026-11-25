<?php

namespace App\Bricks\Fields;

class Text extends Field
{
    protected function typeRules(): array
    {
        return ['string', 'max:255'];
    }

    public function view(): string
    {
        return 'bricks.fields.text';
    }
}
