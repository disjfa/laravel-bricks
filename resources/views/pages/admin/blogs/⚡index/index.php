<?php

use App\Livewire\Concerns\QueriesWithQueryBuilder;
use App\Models\Blog;
use App\Queries\BlogQuery;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin'), Title('Blogs')] class extends Component
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

    public function createBlog(): void
    {
        $this->authorize('create', Blog::class);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $blog = Blog::query()->create($validated);

        $this->redirectRoute('admin.blogs.edit', ['blog' => $blog], navigate: true);
    }

    public function deleteBlog(int $blogId): void
    {
        $blog = Blog::query()->findOrFail($blogId);

        $this->authorize('delete', $blog);

        $blog->delete();

        Flux::modal("delete-blog-{$blogId}")->close();

        Flux::toast(variant: 'success', text: __('Blog deleted.'));
    }

    /**
     * @return LengthAwarePaginator<int, Blog>
     */
    #[Computed]
    public function blogs(): LengthAwarePaginator
    {
        $this->authorize('viewAny', Blog::class);

        return BlogQuery::for(Blog::query(), $this->queryRequest(BlogQuery::class, ['search' => $this->search]))
            ->paginate(15);
    }
};
