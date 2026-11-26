<?php

namespace App\Queries;

use Spatie\QueryBuilder\AllowedFilter;

class PageQuery extends ModelQuery
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
        return ['seqnr', 'title', 'slug', 'published_at', 'created_at', 'updated_at'];
    }

    protected function defaultSort(): string
    {
        return 'seqnr';
    }

    protected function includes(): array
    {
        return ['bricks'];
    }
}
