<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

/**
 * The name and logo of the website, set on the admin settings page.
 */
class SiteSettings extends Settings
{
    public ?string $title;

    /**
     * The path of the uploaded logo on the public disk.
     */
    public ?string $logo;

    public static function group(): string
    {
        return 'site';
    }

    /**
     * The name shown next to the logo and in the footer, the app name when none is set.
     */
    public function displayTitle(): string
    {
        return filled($this->title) ? $this->title : (string) config('app.name', 'Laravel');
    }

    /**
     * The uploaded logo, or null for the default logo.
     */
    public function logoUrl(): ?string
    {
        return filled($this->logo) ? Storage::disk('public')->url($this->logo) : null;
    }
}
