<?php

namespace App\Bricks;

use App\Fields\Image;
use App\Fields\Link;
use App\Fields\Text;
use App\Fields\Textarea;

class HeroBrick extends BaseBrick
{
    public function key(): string
    {
        return 'hero';
    }

    public function label(): string
    {
        return __('Hero');
    }

    public function icon(): string
    {
        return 'sparkles';
    }

    public function order(): int
    {
        return 10;
    }

    public function fields(): array
    {
        return [
            Text::make('title')->label(__('Title'))->required(),
            Textarea::make('subtitle')->label(__('Subtitle'))->rows(2)->maxLength(1000),
            Image::make('image_url')->label(__('Background image')),
            Text::make('button_label')->label(__('Button label'))->half()->rules(['required_with:button_url']),
            Link::make('button_url')->label(__('Button link'))->half()->rules(['required_with:button_label']),
        ];
    }
}
