<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

/**
 * Site-wide switch, turned on or off on the admin settings page.
 */
#[Name('site-menu')]
class SiteMenu implements SiteFeature
{
    /**
     * Enabled until an admin turns it off.
     */
    public function resolve(mixed $scope): bool
    {
        return true;
    }

    public function label(): string
    {
        return __('Site menu');
    }

    public function description(): string
    {
        return __('Build menus and show them in the header and footer of the website.');
    }
}
