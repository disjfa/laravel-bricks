<?php

namespace App\Fields;

use App\Rules\LinkUrl;

class Link extends Field
{
    protected ?string $placeholder = '/about';

    protected function typeRules(): array
    {
        return ['string', 'max:2048', new LinkUrl];
    }

    public function view(): string
    {
        return 'fields.text';
    }
}
