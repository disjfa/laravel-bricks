<?php

namespace App\Bricks\Fields;

class Link extends Field
{
    /**
     * Relative links, anchors, mail links or http(s) URLs, which keeps `javascript:` URLs out of the rendered page.
     */
    protected const string Pattern = 'regex:/^(\/|#|https?:\/\/|mailto:)/i';

    protected ?string $placeholder = '/about';

    protected function typeRules(): array
    {
        return ['string', 'max:2048', self::Pattern];
    }

    public function view(): string
    {
        return 'bricks.fields.text';
    }
}
