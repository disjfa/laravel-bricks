<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

/**
 * Site-wide switch, turned on or off on the admin settings page.
 */
#[Name('blogs')]
class Blogs implements SiteFeature
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
        return __('Blogs');
    }

    public function description(): string
    {
        return __('Write blogs, show them at /blog/{slug} and list them with the blogs brick.');
    }
}
