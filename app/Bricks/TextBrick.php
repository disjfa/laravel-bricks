<?php

namespace App\Bricks;

use App\Fields\Markdown;

class TextBrick extends BaseBrick
{
    public function key(): string
    {
        return 'text';
    }

    public function label(): string
    {
        return __('Text');
    }

    public function icon(): string
    {
        return 'bars-3-bottom-left';
    }

    public function order(): int
    {
        return 30;
    }

    public function fields(): array
    {
        return [
            Markdown::make('body')->label(__('Text'))->required(),
        ];
    }
}
