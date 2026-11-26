<?php

namespace App\Enums;

enum ColorScheme: string
{
    case Light = 'light';
    case Dark = 'dark';

    public function label(): string
    {
        return match ($this) {
            self::Light => __('Light'),
            self::Dark => __('Dark'),
        };
    }
}
