<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * How rounded the corners of the website are (App\Enums\SiteCorners), medium: half of Tailwind's own sizes.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('theme.corners', 'medium');
    }
};
