<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelSettings\Settings;

/**
 * The web app manifest settings, see App\Http\Controllers\ManifestController.
 */
class PwaSettings extends Settings
{
    /**
     * The display modes of the web app manifest that can be chosen.
     */
    public const array DisplayModes = ['standalone', 'minimal-ui', 'browser'];

    /**
     * The directory on the public disk with the generated app icons, see GenerateAppIcons.
     */
    public ?string $icon_directory;

    public ?string $short_name;

    public string $display;

    public static function group(): string
    {
        return 'pwa';
    }

    /**
     * The URL of a generated app icon, like "icon-192.png", or null when no icon is uploaded.
     */
    public function iconUrl(string $fileName): ?string
    {
        return filled($this->icon_directory) ? Storage::disk('public')->url("{$this->icon_directory}/{$fileName}") : null;
    }

    /**
     * The name under the icon on a home screen, short enough to fit there.
     */
    public function shortName(): string
    {
        return filled($this->short_name) ? $this->short_name : Str::limit(app(SiteSettings::class)->displayTitle(), 12, '');
    }

    public function displayMode(): string
    {
        return in_array($this->display, self::DisplayModes, true) ? $this->display : 'standalone';
    }
}
