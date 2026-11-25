<?php

namespace App\Bricks;

use App\Bricks\Fields\Image;
use App\Bricks\Fields\Text;

class ImageBrick extends BaseBrick
{
    public function key(): string
    {
        return 'image';
    }

    public function label(): string
    {
        return __('Image');
    }

    public function icon(): string
    {
        return 'photo';
    }

    public function order(): int
    {
        return 40;
    }

    public function fields(): array
    {
        return [
            Image::make('url')->label(__('Image'))->required(),
            Text::make('alt')->label(__('Alt text'))->half(),
            Text::make('caption')->label(__('Caption'))->half(),
        ];
    }
}
