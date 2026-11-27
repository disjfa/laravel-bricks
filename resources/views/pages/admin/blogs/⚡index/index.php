<?php

use App\Enums\BlogTagType;
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
use Spatie\Tags\Tag;

new #[Layout('layouts::admin'), Title('Blogs')] class extends Component
{
    use QueriesWithQueryBuilder;
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    /**
     * The slug of a category to show, or empty for all blogs.
     */
    #[Url(except: '')]
    public string $category = '';

    /**
     * The slug of a tag to show, or empty for all blogs.
     */
    #[Url(except: '')]
    public string $tag = '';

    public string $title = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedTag(): void
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

        return BlogQuery::for(Blog::query()->with('tags'), $this->queryRequest(BlogQuery::class, [
            'search' => $this->search,
            'category' => $this->category,
            'tag' => $this->tag,
        ]))->paginate(15);
    }

    /**
     * Every category, for the filter: slug => name.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function categoryOptions(): array
    {
        return Tag::getWithType(BlogTagType::Category->value)
            ->whereInstanceOf(Tag::class)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->mapWithKeys(fn (Tag $tag) => [$tag->slug => $tag->name])
            ->all();
    }
};
