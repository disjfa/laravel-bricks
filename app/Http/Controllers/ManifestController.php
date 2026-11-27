<?php

namespace App\Http\Controllers;

use App\Actions\Pwa\GenerateAppIcons;
use App\Enums\ColorScheme;
use App\Enums\ThemeColor;
use App\Settings\PwaSettings;
use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use App\Settings\ThemeSettings;
use Illuminate\Http\JsonResponse;

/**
 * The web app manifest, which lets visitors install the website as an app.
 *
 * @see https://web.dev/articles/add-manifest
 */
class ManifestController extends Controller
{
    public function __invoke(SiteSettings $site, SeoSettings $seo, PwaSettings $pwa, ThemeSettings $theme): JsonResponse
    {
        $manifest = array_filter([
            'id' => '/',
            'name' => $site->displayTitle(),
            'short_name' => $pwa->shortName(),
            'description' => $seo->description(),
            'start_url' => '/',
            'scope' => '/',
            'display' => $pwa->displayMode(),
            'background_color' => $theme->color(ThemeColor::Background, ColorScheme::Light),
            'theme_color' => $theme->color(ThemeColor::MenubarBackground, ColorScheme::Light),
            'icons' => $this->icons($pwa),
        ], fn (mixed $value) => $value !== null);

        return response()
            ->json($manifest, options: JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * @return array<int, array{src: string|null, sizes: string, type: string, purpose?: string}>
     */
    protected function icons(PwaSettings $pwa): array
    {
        if ($pwa->iconUrl('icon-192.png') === null) {
            return [
                ['src' => asset('apple-touch-icon.png'), 'sizes' => '180x180', 'type' => 'image/png'],
                ['src' => asset('favicon.svg'), 'sizes' => 'any', 'type' => 'image/svg+xml'],
            ];
        }

        return [
            ...array_map(fn (int $size) => [
                'src' => $pwa->iconUrl("icon-{$size}.png"),
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any',
            ], GenerateAppIcons::IconSizes),
            ...array_map(fn (int $size) => [
                'src' => $pwa->iconUrl("maskable-{$size}.png"),
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'maskable',
            ], GenerateAppIcons::MaskableSizes),
        ];
    }
}
