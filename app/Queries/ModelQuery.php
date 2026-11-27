<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * The filters, sorts and includes of one model, shared by the API and the admin lists.
 *
 * @example PageQuery::for(Page::query()->published())->paginate() reads ?filter[title]=…&sort=-published_at&include=bricks
 *
 * @phpstan-consistent-constructor
 */
abstract class ModelQuery
{
    /**
     * @return array<int, AllowedFilter|string>
     */
    abstract protected function filters(): array;

    /**
     * @return array<int, AllowedSort|string>
     */
    abstract protected function sorts(): array;

    /**
     * The sort when none is asked for, like "-published_at".
     */
    abstract protected function defaultSort(): string;

    /**
     * @return array<int, AllowedInclude|string>
     */
    protected function includes(): array
    {
        return [];
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>|Relation<TModel, *, *>|class-string<TModel>  $subject
     * @param  Request|null  $request  the request to read the filters from, the current request by default
     * @return QueryBuilder<TModel>
     */
    public static function for(Builder|Relation|string $subject, ?Request $request = null): QueryBuilder
    {
        $definition = new static;

        $query = QueryBuilder::for($subject, $request)
            ->allowedFilters(...$definition->filters())
            ->allowedSorts(...$definition->sorts())
            ->defaultSort($definition->defaultSort());

        if ($includes = $definition->includes()) {
            $query->allowedIncludes(...$includes);
        }

        return $query;
    }

    /**
     * The names that can be used in ?sort=, without the "-" for descending.
     *
     * @return array<int, string>
     */
    public static function sortNames(): array
    {
        return collect((new static)->sorts())
            ->map(fn (AllowedSort|string $sort) => is_string($sort) ? $sort : $sort->getName())
            ->all();
    }

    /**
     * A "search" filter that looks for the value in any of the columns.
     *
     * @param  array<int, string>  $columns
     */
    protected static function search(array $columns): AllowedFilter
    {
        return AllowedFilter::callback('search', function (Builder $query, mixed $value) use ($columns): void {
            // The query builder splits values on commas, a search for "a, b" is one search.
            $search = is_array($value) ? implode(',', $value) : (string) $value;

            $query->where(function (Builder $query) use ($columns, $search): void {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            });
        });
    }
}
