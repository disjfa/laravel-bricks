<?php

namespace App\Queries;

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class FormQuery extends ModelQuery
{
    protected function filters(): array
    {
        return [
            self::search(['name']),
            AllowedFilter::partial('name'),
        ];
    }

    protected function sorts(): array
    {
        return [
            'name',
            'created_at',
            AllowedSort::field('submissions', 'submissions_count'),
        ];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }
}
