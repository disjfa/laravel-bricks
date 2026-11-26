<?php

namespace App\Enums;

enum MenuItemType: string
{
    case Page = 'page';
    case Blog = 'blog';
    case Url = 'url';

    public function label(): string
    {
        return match ($this) {
            self::Page => __('Page'),
            self::Blog => __('Blog'),
            self::Url => __('Custom link'),
        };
    }
}
