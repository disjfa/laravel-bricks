<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * The SEO defaults for pages and blogs that have no SEO data of their own, see App\Concerns\HasSeo.
 */
class SeoSettings extends Settings
{
    public ?string $meta_title;

    public ?string $meta_description;

    /**
     * The share image for pages and blogs without one of their own.
     */
    public ?string $meta_image;

    /**
     * Hide the whole website from search engines.
     */
    public bool $noindex;

    public static function group(): string
    {
        return 'seo';
    }

    /**
     * The browser title of the home page and the part after the page title elsewhere ("About - Meta title").
     */
    public function title(): string
    {
        return filled($this->meta_title) ? $this->meta_title : app(SiteSettings::class)->displayTitle();
    }

    public function description(): ?string
    {
        return filled($this->meta_description) ? $this->meta_description : null;
    }

    public function image(): ?string
    {
        return filled($this->meta_image) ? $this->meta_image : null;
    }
}
