<?php

namespace App\Queries;

use Spatie\QueryBuilder\AllowedFilter;

class MenuQuery extends ModelQuery
{
    protected function filters(): array
    {
        return [
            self::search(['name', 'handle']),
            AllowedFilter::partial('name'),
            AllowedFilter::exact('handle'),
        ];
    }

    protected function sorts(): array
    {
        return ['name', 'handle', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }

    protected function includes(): array
    {
        return ['items'];
    }
}
