<?php

use App\Livewire\Concerns\QueriesWithQueryBuilder;
use App\Models\User;
use App\Queries\UserQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin'), Title('Users')] class extends Component
{
    use QueriesWithQueryBuilder;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        $this->authorize('viewAny', User::class);

        return UserQuery::for(User::query()->with('roles'), $this->queryRequest(UserQuery::class, ['search' => $this->search]))
            ->paginate(15);
    }
};
