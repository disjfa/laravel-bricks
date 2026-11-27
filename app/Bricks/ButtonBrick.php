<?php

namespace App\Bricks;

use App\Fields\Link;
use App\Fields\Select;
use App\Fields\Text;

class ButtonBrick extends BaseBrick
{
    public function key(): string
    {
        return 'button';
    }

    public function label(): string
    {
        return __('Button');
    }

    public function icon(): string
    {
        return 'cursor-arrow-rays';
    }

    public function order(): int
    {
        return 60;
    }

    public function fields(): array
    {
        return [
            Text::make('label')->label(__('Label'))->required()->half(),
            Link::make('url')->label(__('Link'))->required()->half(),
            Select::make('variant')->label(__('Style'))->required()->options([
                'primary' => __('Primary'),
                'outline' => __('Outline'),
            ]),
        ];
    }
}
