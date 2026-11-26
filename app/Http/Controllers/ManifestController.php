<?php

namespace App\Http\Controllers;

use App\Actions\Pwa\GenerateAppIcons;
use App\Enums\ColorScheme;
use App\Enums\ThemeColor;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

/**
 * The web app manifest, which lets visitors install the website as an app.
 *
 * @see https://web.dev/articles/add-manifest
 */
class ManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $palette = ThemeColor::palette()[ColorScheme::Light->value];

        $manifest = array_filter([
            'id' => '/',
            'name' => Setting::siteTitle(),
            'short_name' => Setting::appShortName(),
            'description' => Setting::metaDescription(),
            'start_url' => '/',
            'scope' => '/',
            'display' => Setting::appDisplay(),
            'background_color' => $palette[ThemeColor::Background->value],
            'theme_color' => $palette[ThemeColor::MenubarBackground->value],
            'icons' => $this->icons(),
        ], fn (mixed $value) => $value !== null);

        return response()
            ->json($manifest, options: JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * @return array<int, array{src: string, sizes: string, type: string, purpose?: string}>
     */
    protected function icons(): array
    {
        if (Setting::appIconUrl('icon-192.png') === null) {
            return [
                ['src' => asset('apple-touch-icon.png'), 'sizes' => '180x180', 'type' => 'image/png'],
                ['src' => asset('favicon.svg'), 'sizes' => 'any', 'type' => 'image/svg+xml'],
            ];
        }

        return [
            ...array_map(fn (int $size) => [
                'src' => Setting::appIconUrl("icon-{$size}.png"),
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any',
            ], GenerateAppIcons::IconSizes),
            ...array_map(fn (int $size) => [
                'src' => Setting::appIconUrl("maskable-{$size}.png"),
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'maskable',
            ], GenerateAppIcons::MaskableSizes),
        ];
    }
}
