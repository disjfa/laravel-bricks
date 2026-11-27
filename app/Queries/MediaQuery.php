<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;

class MediaQuery extends ModelQuery
{
    protected function filters(): array
    {
        return [
            self::search(['name', 'file_name']),
            AllowedFilter::callback('type', function (Builder $query, mixed $value): void {
                match ($value) {
                    'images' => $query->where('mime_type', 'like', 'image/%'),
                    'documents' => $query->where('mime_type', 'not like', 'image/%'),
                    default => null,
                };
            }),
        ];
    }

    protected function sorts(): array
    {
        return ['name', 'size', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return '-created_at';
    }
}
