<?php

namespace App\Queries;

use Spatie\QueryBuilder\AllowedFilter;

class BlogQuery extends ModelQuery
{
    protected function filters(): array
    {
        return [
            self::search(['title', 'slug']),
            AllowedFilter::partial('title'),
            AllowedFilter::exact('slug'),
            AllowedFilter::exact('status'),
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
}
