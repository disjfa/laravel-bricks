<?php

namespace App\Bricks;

use App\Fields\Select;
use App\Fields\Text;

class HeadingBrick extends BaseBrick
{
    public function key(): string
    {
        return 'heading';
    }

    public function label(): string
    {
        return __('Heading');
    }

    public function icon(): string
    {
        return 'h1';
    }

    public function order(): int
    {
        return 20;
    }

    public function fields(): array
    {
        return [
            Text::make('text')->label(__('Heading'))->required(),
            Select::make('level')->label(__('Level'))->required()->options([1 => 'H1', 2 => 'H2', 3 => 'H3', 4 => 'H4']),
        ];
    }
}
