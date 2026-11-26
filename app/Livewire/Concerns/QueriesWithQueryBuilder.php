<?php

namespace App\Livewire\Concerns;

use App\Queries\ModelQuery;
use Illuminate\Http\Request;
use Livewire\Attributes\Url;

/**
 * Sortable columns and filters for an admin list, with the same definitions as the API (App\Queries).
 * The component passes its properties as the query: $this->queryRequest(PageQuery::class, ['search' => $this->search]).
 * Use it together with Livewire\WithPagination.
 */
trait QueriesWithQueryBuilder
{
    /**
     * The sort in query builder notation, like "title" or "-updated_at". Empty for the default sort.
     */
    #[Url(except: '')]
    public string $sort = '';

    /**
     * Sort by the column, or flip the direction when it is sorted by it already.
     */
    public function sortBy(string $column): void
    {
        $this->sort = $this->sort === $column ? "-{$column}" : $column;

        $this->resetPage();
    }

    /**
     * "asc" or "desc" when the list is sorted by the column, null otherwise. For <flux:table.column>.
     */
    public function sortDirection(string $column): ?string
    {
        return match ($this->sort) {
            $column => 'asc',
            "-{$column}" => 'desc',
            default => null,
        };
    }

    /**
     * A request with the filters and sort of the component, for ModelQuery::for(). A sort that is not
     * allowed (from an edited URL) is left out, so the list falls back to its default sort.
     *
     * @param  class-string<ModelQuery>  $queryClass
     * @param  array<string, mixed>  $filters
     */
    protected function queryRequest(string $queryClass, array $filters = []): Request
    {
        $sort = in_array(ltrim($this->sort, '-'), $queryClass::sortNames(), true) ? $this->sort : null;

        return Request::create('/', 'GET', array_filter([
            'filter' => array_filter($filters, fn (mixed $value) => filled($value)),
            'sort' => $sort,
        ]));
    }
}
