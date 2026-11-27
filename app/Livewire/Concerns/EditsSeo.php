<?php

namespace App\Livewire\Concerns;

use App\Fields\ShareImage;

/**
 * The SEO fields of a Livewire component: the page and blog settings, and the site settings
 * (where they are the defaults). Render them with <x-admin.seo-fields>.
 */
trait EditsSeo
{
    public string $metaTitle = '';

    public string $metaDescription = '';

    public string $metaImage = '';

    public bool $noindex = false;

    /**
     * @param  array{meta_title?: string|null, meta_description?: string|null, meta_image?: string|null, noindex?: bool|null}  $values
     */
    protected function fillSeo(array $values): void
    {
        $this->metaTitle = (string) ($values['meta_title'] ?? '');
        $this->metaDescription = (string) ($values['meta_description'] ?? '');
        $this->metaImage = (string) ($values['meta_image'] ?? '');
        $this->noindex = (bool) ($values['noindex'] ?? false);
    }

    /**
     * Validates the SEO fields and returns them as attributes, empty fields as null.
     *
     * @return array{meta_title: string|null, meta_description: string|null, meta_image: string|null, noindex: bool}
     */
    protected function validatedSeo(): array
    {
        $this->validate([
            'metaTitle' => ['nullable', 'string', 'max:255'],
            'metaDescription' => ['nullable', 'string', 'max:300'],
            'metaImage' => ShareImage::make('metaImage')->getRules(),
            'noindex' => ['boolean'],
        ], [], [
            'metaTitle' => __('meta title'),
            'metaDescription' => __('meta description'),
            'metaImage' => __('share image'),
        ]);

        return [
            'meta_title' => trim($this->metaTitle) ?: null,
            'meta_description' => trim($this->metaDescription) ?: null,
            'meta_image' => trim($this->metaImage) ?: null,
            'noindex' => $this->noindex,
        ];
    }
}
