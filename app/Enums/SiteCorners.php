<?php

namespace App\Enums;

/**
 * How rounded the corners of the website are, chosen on the admin settings page (App\Settings\ThemeSettings).
 * Scales the radius sizes of Tailwind (rounded-sm up to rounded-4xl), so the bricks and the Flux inputs and
 * buttons follow it. rounded-full stays round.
 */
enum SiteCorners: string
{
    case Square = 'square';
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';

    /**
     * The radius sizes of Tailwind in rem, as the Large corners use them.
     */
    public const array Radii = [
        '--radius-xs' => 0.125,
        '--radius-sm' => 0.25,
        '--radius-md' => 0.375,
        '--radius-lg' => 0.5,
        '--radius-xl' => 0.75,
        '--radius-2xl' => 1,
        '--radius-3xl' => 1.5,
        '--radius-4xl' => 2,
    ];

    public function label(): string
    {
        return match ($this) {
            self::Square => __('None'),
            self::Small => __('Small'),
            self::Medium => __('Medium'),
            self::Large => __('Large'),
        };
    }

    /**
     * How much the Tailwind radius sizes are multiplied by. A card (rounded-2xl, 1rem) gets 0.1rem with small corners,
     * 0.5rem with medium and 1rem with large.
     */
    public function scale(): float
    {
        return match ($this) {
            self::Square => 0,
            self::Small => 0.1,
            self::Medium => 0.5,
            self::Large => 1,
        };
    }

    /**
     * The radius sizes as CSS custom properties, name => value.
     *
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        return collect(self::Radii)
            ->map(fn (int|float $rem) => round($rem * $this->scale(), 4).'rem')
            ->all();
    }

    /**
     * The same as cssVariables(), for a style attribute.
     */
    public function style(): string
    {
        return collect($this->cssVariables())
            ->map(fn (string $value, string $name) => "{$name}: {$value};")
            ->implode(' ');
    }
}
