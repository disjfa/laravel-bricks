<?php

namespace App\Bricks;

use App\Enums\BlogTagType;
use App\Features\Blogs;
use App\Fields\Select;
use App\Fields\Text;
use Laravel\Pennant\Feature;
use Spatie\Tags\Tag;

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
            // + keeps the category ids as keys, a spread would renumber them.
            Select::make('category')->label(__('Category'))->options(
                ['' => __('All categories')] + Tag::getWithType(BlogTagType::Category->value)
                    ->whereInstanceOf(Tag::class)
                    ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                    ->mapWithKeys(fn (Tag $category) => [$category->id => $category->name])
                    ->all(),
            ),
        ];
    }
}
