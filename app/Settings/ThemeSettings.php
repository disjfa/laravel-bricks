<?php

namespace App\Settings;

use App\Enums\ColorScheme;
use App\Enums\ThemeColor;
use Spatie\LaravelSettings\Settings;

/**
 * The colors of the website and the admin, for the light and the dark scheme.
 */
class ThemeSettings extends Settings
{
    /**
     * @var array<string, string> color => hex
     */
    public array $light;

    /**
     * @var array<string, string> color => hex
     */
    public array $dark;

    public static function group(): string
    {
        return 'theme';
    }

    /**
     * Every color of both schemes: the saved value, or the default.
     *
     * @return array<string, array<string, string>> scheme => color => hex
     */
    public function palette(): array
    {
        return collect(ColorScheme::cases())
            ->mapWithKeys(fn (ColorScheme $scheme) => [$scheme->value => $this->colors($scheme)])
            ->all();
    }

    /**
     * @return array<string, string> color => hex
     */
    public function colors(ColorScheme $scheme): array
    {
        $saved = $scheme === ColorScheme::Light ? $this->light : $this->dark;

        return collect(ThemeColor::cases())
            ->mapWithKeys(function (ThemeColor $color) use ($saved, $scheme) {
                $value = $saved[$color->value] ?? null;

                return [$color->value => is_string($value) && preg_match(ThemeColor::Pattern, $value) ? $value : $color->default($scheme)];
            })
            ->all();
    }

    public function color(ThemeColor $color, ColorScheme $scheme): string
    {
        return $this->colors($scheme)[$color->value];
    }
}
