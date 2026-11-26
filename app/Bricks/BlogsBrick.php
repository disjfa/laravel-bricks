<?php

namespace App\Bricks;

use App\Features\Blogs;
use App\Fields\Select;
use App\Fields\Text;
use Laravel\Pennant\Feature;

/**
 * The blog archive: published blogs, paginated by a Livewire component.
 */
class BlogsBrick extends BaseBrick
{
    public function key(): string
    {
        return 'blogs';
    }

    public function label(): string
    {
        return __('Blogs');
    }

    public function icon(): string
    {
        return 'newspaper';
    }

    public function order(): int
    {
        return 80;
    }

    public function isAvailable(): bool
    {
        return Feature::active(Blogs::class);
    }

    public function fields(): array
    {
        return [
            Text::make('title')->label(__('Title')),
            Select::make('per_page')->label(__('Blogs per page'))->half()->required()->options([
                6 => '6',
                3 => '3',
                9 => '9',
                12 => '12',
            ]),
            Select::make('sort')->label(__('Order'))->half()->required()->options([
                'newest' => __('Newest first'),
                'sequence' => __('By sequence number'),
            ]),
        ];
    }
}
