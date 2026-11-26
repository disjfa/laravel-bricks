<?php

namespace App\Enums;

enum PublishStatus: string
{
    case Concept = 'concept';
    case Active = 'active';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Concept => __('Concept'),
            self::Active => __('Active'),
            self::Closed => __('Closed'),
        };
    }
}
