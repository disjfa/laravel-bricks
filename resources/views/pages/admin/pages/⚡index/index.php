<?php

use App\Livewire\Concerns\QueriesWithQueryBuilder;
use App\Models\Page;
use App\Queries\PageQuery;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin'), Title('Pages')] class extends Component
{
    use QueriesWithQueryBuilder;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public string $title = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createPage(): void
    {
        $this->authorize('create', Page::class);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $page = Page::query()->create($validated);

        $this->redirectRoute('admin.pages.edit', ['page' => $page], navigate: true);
    }

    public function deletePage(int $pageId): void
    {
        $page = Page::query()->findOrFail($pageId);

        $this->authorize('delete', $page);

        $page->deleteWithRevision();

        Flux::modal("delete-page-{$pageId}")->close();

        Flux::toast(variant: 'success', text: __('Page deleted.'));
    }

    /**
     * @return LengthAwarePaginator<int, Page>
     */
    #[Computed]
    public function pages(): LengthAwarePaginator
    {
        $this->authorize('viewAny', Page::class);

        return PageQuery::for(Page::query()->withCount('bricks'), $this->queryRequest(PageQuery::class, ['search' => $this->search]))
            ->paginate(15);
    }
};
