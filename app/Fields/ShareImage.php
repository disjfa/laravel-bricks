<?php

namespace App\Fields;

/**
 * The image shown when a link to the site is shared on social media.
 */
class ShareImage extends Image
{
    public static function make(string $name): static
    {
        return parent::make($name)
            ->label(__('Share image'))
            ->help(__('Shown when the link is shared on social media, ideally 1200 × 630 pixels.'));
    }
}
