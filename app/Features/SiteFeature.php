<?php

namespace App\Features;

/**
 * A Pennant feature that admins can turn on or off on the settings page.
 * Features are checked globally (see AppServiceProvider), not per user.
 */
interface SiteFeature
{
    public const array All = [
        Blogs::class,
        Forms::class,
        SiteMenu::class,
    ];

    public function resolve(mixed $scope): bool;

    public function label(): string;

    public function description(): string;
}
