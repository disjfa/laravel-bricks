<?php

use App\Features\Blogs;
use App\Models\Blog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Laravel\Pennant\Feature;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The published blogs, rendered by the blogs brick. Paginates without a page reload.
 */
new class extends Component {
    use WithPagination;

    #[Locked]
    public int $perPage = 6;

    /**
     * One of: newest, sequence.
     */
    #[Locked]
    public string $sort = 'newest';

    public function mount(int $perPage = 6, string $sort = 'newest'): void
    {
        $this->perPage = max(1, min($perPage, 48));
        $this->sort = $sort === 'sequence' ? 'sequence' : 'newest';
    }

    /**
     * @return LengthAwarePaginator<int, Blog>
     */
    #[Computed]
    public function blogs(): LengthAwarePaginator
    {
        return Blog::query()
            ->published()
            ->when(Feature::inactive(Blogs::class), fn ($query) => $query->whereRaw('1 = 0'))
            ->when(
                $this->sort === 'sequence',
                fn ($query) => $query->ordered(),
                fn ($query) => $query->orderByRaw('coalesce(published_at, created_at) desc')->orderByDesc('id'),
            )
            ->paginate($this->perPage, pageName: 'blogs');
    }
}; ?>

<div class="space-y-8" data-test="blog-archive">
    @if ($this->blogs->isEmpty())
        <p class="text-zinc-500 dark:text-zinc-400">{{ __('There are no blogs yet.') }}</p>
    @else
        <div class="grid gap-6 sm:grid-cols-2">
            @foreach ($this->blogs as $blog)
                <article wire:key="blog-{{ $blog->id }}" class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white transition hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800">
                    <a href="{{ $blog->url() }}" class="block">
                        @if (filled($imageUrl = $blog->image_url))
                            <img src="{{ $imageUrl }}" alt="" class="aspect-video w-full object-cover" loading="lazy">
                        @endif

                        <div class="space-y-2 p-5">
                            @if ($date = $blog->displayDate())
                                <time datetime="{{ $date->toDateString() }}" class="text-sm text-zinc-500 dark:text-zinc-400">{{ $date->isoFormat('LL') }}</time>
                            @endif

                            <h3 class="text-lg font-semibold text-zinc-900 group-hover:underline dark:text-white">{{ $blog->title }}</h3>

                            @if (filled($blog->excerpt))
                                <p class="text-zinc-600 dark:text-zinc-300">{{ $blog->excerpt }}</p>
                            @endif
                        </div>
                    </a>
                </article>
            @endforeach
        </div>

        <flux:pagination :paginator="$this->blogs" />
    @endif
</div>
