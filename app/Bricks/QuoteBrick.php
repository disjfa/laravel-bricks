<?php

namespace App\Bricks;

use App\Fields\Text;
use App\Fields\Textarea;

class QuoteBrick extends BaseBrick
{
    public function key(): string
    {
        return 'quote';
    }

    public function label(): string
    {
        return __('Quote');
    }

    public function icon(): string
    {
        return 'chat-bubble-bottom-center-text';
    }

    public function order(): int
    {
        return 50;
    }

    public function fields(): array
    {
        return [
            Textarea::make('quote')->label(__('Quote'))->rows(2)->required(),
            Text::make('citation')->label(__('Citation')),
        ];
    }
}
