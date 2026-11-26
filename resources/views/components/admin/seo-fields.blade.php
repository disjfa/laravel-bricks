@props(['imageMedia' => [], 'mediaUrl' => null, 'isDefault' => false, 'metaImage' => ''])

{{--
    The fields of App\Livewire\Concerns\EditsSeo, pass the current share image as metaImage. With isDefault they are the defaults on the site settings,
    otherwise they belong to a page or blog and fall back to those defaults.
--}}
<div {{ $attributes->class('space-y-6') }}>
    <flux:input
        wire:model="metaTitle"
        :label="$isDefault ? __('Default meta title') : __('Meta title')"
        :placeholder="$isDefault ? app(App\Settings\SiteSettings::class)->displayTitle() : null"
        :description:trailing="$isDefault
            ? __('The browser title of the home page, and added after the title of other pages.')
            : __('Replaces the whole browser title. Leave empty for the title followed by the default meta title.')"
    />

    <flux:textarea
        wire:model="metaDescription"
        :label="$isDefault ? __('Default meta description') : __('Meta description')"
        :description:trailing="$isDefault
            ? __('Used for pages and blogs without a meta description. Keep it under 160 characters.')
            : __('Shown by search engines. Leave empty for the default. Keep it under 160 characters.')"
        rows="3"
    />

    @include('fields.image', [
        'field' => App\Fields\ShareImage::make('metaImage'),
        'path' => 'metaImage',
        'value' => $metaImage,
        'imageMedia' => $imageMedia,
        'mediaUrl' => $mediaUrl,
    ])

    <flux:switch
        wire:model="noindex"
        :label="$isDefault ? __('Hide the website from search engines') : __('Hide from search engines')"
        :description="$isDefault
            ? __('Adds noindex to every page, useful while the site is not finished.')
            : __('Adds noindex, so search engines leave this out of their results.')"
        align="left"
    />
</div>
