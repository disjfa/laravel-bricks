<?php

namespace App\Queries;

use Spatie\QueryBuilder\AllowedFilter;

class UserQuery extends ModelQuery
{
    protected function filters(): array
    {
        return [
            self::search(['name', 'email']),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('email'),
        ];
    }

    protected function sorts(): array
    {
        return ['name', 'email', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }
}
