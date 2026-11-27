<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * The defaults of the settings in App\Settings, changed on the admin settings page.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('site.title', null);
        $this->migrator->add('site.logo', null);

        $this->migrator->add('seo.meta_title', null);
        $this->migrator->add('seo.meta_description', null);
        $this->migrator->add('seo.meta_image', null);
        $this->migrator->add('seo.noindex', false);

        $this->migrator->add('pwa.icon_directory', null);
        $this->migrator->add('pwa.short_name', null);
        $this->migrator->add('pwa.display', 'standalone');

        $this->migrator->add('theme.light', []);
        $this->migrator->add('theme.dark', []);

        $this->migrator->add('fonts.text', null);
        $this->migrator->add('fonts.heading', null);
    }
};
