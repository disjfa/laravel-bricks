<?php

namespace App\Features;

use Laravel\Pennant\Attributes\Name;

/**
 * Site-wide switch, turned on or off on the admin settings page.
 */
#[Name('forms')]
class Forms implements SiteFeature
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
        return __('Forms');
    }

    public function description(): string
    {
        return __('Build forms, place them with the form brick and read the submissions.');
    }
}
