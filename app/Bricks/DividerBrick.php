<?php

namespace App\Bricks;

class DividerBrick extends BaseBrick
{
    public function key(): string
    {
        return 'divider';
    }

    public function label(): string
    {
        return __('Divider');
    }

    public function icon(): string
    {
        return 'minus';
    }

    public function order(): int
    {
        return 70;
    }

    public function fields(): array
    {
        return [];
    }
}
