<?php

namespace App\Actions\Pwa;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

/**
 * Turns one uploaded square image into every icon the website needs: the web app manifest icons,
 * maskable icons for Android, Apple touch icons and PNG favicons.
 *
 * The icons land in a new directory on the public disk, so browsers never keep showing an old icon.
 */
class GenerateAppIcons
{
    /**
     * Manifest icons with purpose "any", named icon-{size}.png.
     */
    public const array IconSizes = [48, 72, 96, 128, 144, 152, 192, 256, 384, 512];

    /**
     * Manifest icons with purpose "maskable", named maskable-{size}.png. Android may crop these
     * to a circle or another shape, so the icon is scaled into the safe zone on a background.
     */
    public const array MaskableSizes = [192, 512];

    /**
     * Apple touch icons for the home screen of iPhone (180) and iPad (167, 152), named
     * apple-touch-icon-{size}.png. iOS fills transparency with black, so they get a background.
     */
    public const array AppleSizes = [180, 167, 152, 120];

    public const array FaviconSizes = [16, 32];

    public const string SourceFileName = 'source';

    /**
     * The share of a maskable icon that stays visible in every shape (the safe zone is 80%).
     */
    protected const float MaskableSafeZone = 0.8;

    /**
     * @return string The directory on the public disk that holds the icons.
     */
    public function handle(string $sourcePath, string $backgroundColor): string
    {
        $directory = 'app-icons/'.Str::lower(Str::random(12));
        $disk = Storage::disk('public');
        $disk->makeDirectory($directory);

        // The original, to generate the icons again when the background color changes.
        $disk->putFileAs($directory, new File($sourcePath), self::SourceFileName);

        foreach (self::IconSizes as $size) {
            Image::load($sourcePath)
                ->fit(Fit::Crop, $size, $size)
                ->save($disk->path("{$directory}/icon-{$size}.png"));
        }

        foreach (self::FaviconSizes as $size) {
            Image::load($sourcePath)
                ->fit(Fit::Crop, $size, $size)
                ->save($disk->path("{$directory}/favicon-{$size}.png"));
        }

        foreach (self::MaskableSizes as $size) {
            $innerSize = (int) round($size * self::MaskableSafeZone);

            Image::load($sourcePath)
                ->fit(Fit::Crop, $innerSize, $innerSize)
                ->resizeCanvas($size, $size, AlignPosition::Center, false, $backgroundColor)
                ->save($disk->path("{$directory}/maskable-{$size}.png"));
        }

        foreach (self::AppleSizes as $size) {
            Image::load($sourcePath)
                ->fit(Fit::Crop, $size, $size)
                ->background($backgroundColor)
                ->save($disk->path("{$directory}/apple-touch-icon-{$size}.png"));
        }

        return $directory;
    }
}
