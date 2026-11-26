<?php

namespace App\Fields;

/**
 * An image URL, picked from the page media or typed in.
 */
class Image extends Field
{
    /**
     * Site-relative paths (like media library files) or http(s) URLs.
     */
    protected const string Pattern = 'regex:/^(\/|https?:\/\/)\S+$/i';

    protected ?string $placeholder = 'https:// or /storage/...';

    protected function typeRules(): array
    {
        return ['string', 'max:2048', self::Pattern];
    }

    public function view(): string
    {
        return 'fields.image';
    }
}
