<?php

namespace App\Enums;

enum BrickWidth: string
{
    case Normal = 'normal';
    case Wide = 'wide';
    case Full = 'full';

    public function label(): string
    {
        return match ($this) {
            self::Normal => __('Normal'),
            self::Wide => __('Wide'),
            self::Full => __('Full width'),
        };
    }

    /**
     * The width of the brick from its settings, normal when it has none.
     *
     * @param  array<string, mixed>|null  $settings
     */
    public static function fromSettings(?array $settings): self
    {
        return self::tryFrom((string) ($settings['width'] ?? '')) ?? self::Normal;
    }

    /**
     * Classes for the element that wraps the brick on the website.
     */
    public function containerClass(): string
    {
        return match ($this) {
            self::Normal => 'mx-auto w-full max-w-3xl px-6',
            self::Wide => 'mx-auto w-full max-w-6xl px-6',
            self::Full => 'w-full px-6',
        };
    }
}
