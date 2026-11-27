<?php

namespace App\Queries;

use App\Enums\BlogTagType;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\Tags\Tag;

class BlogQuery extends ModelQuery
{
    protected function filters(): array
    {
        return [
            self::search(['title', 'slug']),
            AllowedFilter::partial('title'),
            AllowedFilter::exact('slug'),
            AllowedFilter::exact('status'),
            self::tagFilter(BlogTagType::Category),
            self::tagFilter(BlogTagType::Tag),
        ];
    }

    protected function sorts(): array
    {
        return ['published_at', 'seqnr', 'title', 'slug', 'created_at', 'updated_at'];
    }

    protected function defaultSort(): string
    {
        return '-published_at';
    }

    protected function includes(): array
    {
        return ['bricks'];
    }

    /**
     * ?filter[category]=news or ?filter[tag]=laravel, by slug. More slugs, separated by commas, match any of them.
     */
    protected static function tagFilter(BlogTagType $type): AllowedFilter
    {
        return AllowedFilter::callback($type->value, function (Builder $query, mixed $value) use ($type): void {
            $query->whereHas('tags', fn (Builder $tags) => $tags
                ->where('type', $type->value)
                ->whereIn('slug->'.Tag::getLocale(), (array) $value));
        });
    }
}
