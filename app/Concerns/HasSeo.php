<?php

namespace App\Concerns;

use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use romanzipp\Seo\Structs\Meta;

/**
 * The SEO data of a page or blog (meta_title, meta_description, meta_image and noindex), written to
 * the head of the website with romanzipp/laravel-seo. Fields that are empty fall back to the content
 * of the model and then to the defaults from the admin settings.
 *
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_image
 * @property bool $noindex
 */
trait HasSeo
{
    public function initializeHasSeo(): void
    {
        $this->mergeCasts(['noindex' => 'boolean']);
    }

    /**
     * The browser title: the meta title as entered, or the title followed by the default meta title.
     */
    public function seoTitle(): string
    {
        if (filled($this->meta_title)) {
            return $this->meta_title;
        }

        return $this->title.' - '.app(SeoSettings::class)->title();
    }

    public function seoDescription(): ?string
    {
        return filled($this->meta_description) ? $this->meta_description : app(SeoSettings::class)->description();
    }

    /**
     * The image shown when the page is shared, as an absolute URL.
     */
    public function seoImage(): ?string
    {
        $image = collect([$this->meta_image, $this->fallbackSeoImage(), app(SeoSettings::class)->image()])->first(fn (?string $image) => filled($image));

        return filled($image) ? url($image) : null;
    }

    protected function fallbackSeoImage(): ?string
    {
        return null;
    }

    /**
     * The Open Graph type, like "website" or "article".
     */
    protected function seoType(): string
    {
        return 'website';
    }

    /**
     * Search engines may index published content, unless noindex is set on it or on the whole site.
     */
    public function isIndexable(): bool
    {
        return ! $this->noindex && ! app(SeoSettings::class)->noindex && $this->isPublished();
    }

    public function applySeo(): void
    {
        seo()
            ->title($this->seoTitle())
            ->description($this->seoDescription())
            ->canonical($this->url())
            ->og('type', $this->seoType())
            ->og('url', $this->url())
            ->og('site_name', app(SiteSettings::class)->displayTitle())
            ->twitter('card', 'summary_large_image');

        if ($image = $this->seoImage()) {
            seo()->image($image);
        }

        if (! $this->isIndexable()) {
            seo()->add(Meta::make()->name('robots')->content('noindex, nofollow'));
        }
    }
}
