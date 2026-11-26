<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use romanzipp\Seo\Structs\Meta;

/**
 * Site-wide settings from the admin settings page, stored as key and JSON value.
 *
 * @property string $key
 * @property mixed $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected const string CacheKey = 'settings';

    public const string SiteTitleKey = 'site_title';

    /**
     * The path of the uploaded logo on the public disk.
     */
    public const string SiteLogoKey = 'site_logo';

    /**
     * The directory on the public disk with the generated app icons, see GenerateAppIcons.
     */
    public const string AppIconKey = 'app_icon';

    public const string AppShortNameKey = 'app_short_name';

    public const string AppDisplayKey = 'app_display';

    /**
     * The display modes of the web app manifest that can be chosen.
     */
    public const array AppDisplayModes = ['standalone', 'minimal-ui', 'browser'];

    public const string MetaTitleKey = 'meta_title';

    public const string MetaDescriptionKey = 'meta_description';

    public const string MetaImageKey = 'meta_image';

    /**
     * Hide the whole website from search engines.
     */
    public const string NoindexKey = 'noindex';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CacheKey));
        static::deleted(fn () => Cache::forget(self::CacheKey));
    }

    /**
     * Read a setting. All settings are cached together, the website reads them on every page.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever(
            self::CacheKey,
            fn () => static::query()->pluck('value', 'key')->all(),
        );

        return $settings[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * The name of the site, shown next to the logo and in the browser title.
     */
    public static function siteTitle(): string
    {
        $title = static::get(self::SiteTitleKey);

        return filled($title) ? $title : (string) config('app.name', 'Laravel');
    }

    /**
     * The uploaded logo, or null for the default logo.
     */
    public static function siteLogoUrl(): ?string
    {
        $path = static::get(self::SiteLogoKey);

        return filled($path) ? Storage::disk('public')->url($path) : null;
    }

    /**
     * The browser title of the home page and the part after the page title elsewhere ("About - Meta title").
     */
    public static function metaTitle(): string
    {
        $title = static::get(self::MetaTitleKey);

        return filled($title) ? $title : static::siteTitle();
    }

    /**
     * The description for search engines when a page or blog has none of its own.
     */
    public static function metaDescription(): ?string
    {
        $description = static::get(self::MetaDescriptionKey);

        return filled($description) ? $description : null;
    }

    /**
     * The share image for pages and blogs without one of their own.
     */
    public static function metaImage(): ?string
    {
        $image = static::get(self::MetaImageKey);

        return filled($image) ? $image : null;
    }

    public static function isNoindex(): bool
    {
        return (bool) static::get(self::NoindexKey, false);
    }

    /**
     * The SEO defaults in the shape of App\Livewire\Concerns\EditsSeo.
     *
     * @return array{meta_title: string|null, meta_description: string|null, meta_image: string|null, noindex: bool}
     */
    public static function seoDefaults(): array
    {
        return [
            'meta_title' => static::get(self::MetaTitleKey),
            'meta_description' => static::get(self::MetaDescriptionKey),
            'meta_image' => static::get(self::MetaImageKey),
            'noindex' => static::isNoindex(),
        ];
    }

    /**
     * The SEO tags for a website page without SEO data of its own, like the welcome screen.
     */
    public static function applyDefaultSeo(?string $title = null, ?string $description = null): void
    {
        seo()
            ->title(filled($title) ? $title.' - '.static::metaTitle() : static::metaTitle())
            ->og('type', 'website')
            ->og('site_name', static::siteTitle());

        if (filled($description = filled($description) ? $description : static::metaDescription())) {
            seo()->description($description);
        }

        if ($image = static::metaImage()) {
            seo()->image(url($image));
        }

        if (static::isNoindex()) {
            seo()->add(Meta::make()->name('robots')->content('noindex, nofollow'));
        }
    }

    /**
     * The URL of a generated app icon, like "icon-192.png", or null when no icon is uploaded.
     */
    public static function appIconUrl(string $fileName): ?string
    {
        $directory = static::get(self::AppIconKey);

        return filled($directory) ? Storage::disk('public')->url("{$directory}/{$fileName}") : null;
    }

    /**
     * The name under the icon on a home screen, short enough to fit there.
     */
    public static function appShortName(): string
    {
        $shortName = static::get(self::AppShortNameKey);

        return filled($shortName) ? $shortName : Str::limit(static::siteTitle(), 12, '');
    }

    public static function appDisplay(): string
    {
        $display = static::get(self::AppDisplayKey);

        return in_array($display, self::AppDisplayModes, true) ? $display : 'standalone';
    }
}
