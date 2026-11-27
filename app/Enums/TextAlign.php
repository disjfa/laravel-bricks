<?php

namespace App\Enums;

enum TextAlign: string
{
    case Start = 'start';
    case Center = 'center';
    case End = 'end';

    public function label(): string
    {
        return match ($this) {
            self::Start => __('Left'),
            self::Center => __('Center'),
            self::End => __('Right'),
        };
    }

    /**
     * The alignment of the brick from its settings, left when it has none.
     *
     * @param  array<string, mixed>|null  $settings
     */
    public static function fromSettings(?array $settings): self
    {
        return self::tryFrom((string) ($settings['align'] ?? '')) ?? self::Start;
    }

    /**
     * Classes for the text on the website. Start and end follow the reading direction.
     */
    public function textClass(): string
    {
        return match ($this) {
            self::Start => 'text-start',
            self::Center => 'text-center',
            self::End => 'text-end',
        };
    }
}
