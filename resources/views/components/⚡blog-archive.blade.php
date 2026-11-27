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

    /**
     * Only the blogs with this category or tag (spatie/laravel-tags), or all blogs.
     */
    #[Locked]
    public ?int $tagId = null;

    public function mount(int $perPage = 6, string $sort = 'newest', ?int $tagId = null): void
    {
        $this->perPage = max(1, min($perPage, 48));
        $this->sort = $sort === 'sequence' ? 'sequence' : 'newest';
        $this->tagId = $tagId;
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
            ->when($this->tagId, fn ($query, int $tagId) => $query->whereHas('tags', fn ($tags) => $tags->whereKey($tagId)))
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
        {{-- As many columns as fit, at least 16rem each: four in a wide brick, two in a normal one, one on a phone. --}}
        <div class="grid grid-cols-[repeat(auto-fill,minmax(min(16rem,100%),1fr))] gap-6">
            @foreach ($this->blogs as $blog)
                {{--
                    Lifts on hover with the accent color on its border and title, and the image zooms in a little.
                    It presses in while clicked. No movement for those who prefer reduced motion.
                --}}
                <article
                    wire:key="blog-{{ $blog->id }}"
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white transition duration-200 ease-out hover:-translate-y-1 hover:border-accent hover:shadow-lg active:translate-y-0 active:scale-[0.98] active:shadow-sm has-focus-visible:ring-2 has-focus-visible:ring-accent motion-reduce:transform-none motion-reduce:transition-none dark:border-zinc-700 dark:bg-zinc-800"
                    data-test="blog-card"
                >
                    <div class="aspect-video overflow-hidden">
                        @if (filled($imageUrl = $blog->image_url))
                            <img src="{{ $imageUrl }}" alt="" class="size-full object-cover transition duration-300 ease-out group-hover:scale-105 motion-reduce:transform-none" loading="lazy">
                        @else
                            {{-- Without an image the card keeps the same shape, so the cards in a row line up. --}}
                            <div class="size-full bg-linear-to-br from-accent/15 to-accent/5 transition-colors duration-300 group-hover:from-accent/25 group-hover:to-accent/10" data-test="blog-image-placeholder"></div>
                        @endif
                    </div>

                    <div class="space-y-2 p-5">
                        @if ($date = $blog->displayDate())
                            <time datetime="{{ $date->toDateString() }}" class="text-sm text-zinc-500 dark:text-zinc-400">{{ $date->isoFormat('LL') }}</time>
                        @endif

                        <h3 class="text-lg font-semibold text-zinc-900 transition-colors group-hover:text-accent-content dark:text-white dark:group-hover:text-accent-content">
                            {{-- The link covers the whole card. --}}
                            <a href="{{ $blog->url() }}" class="outline-none after:absolute after:inset-0">{{ $blog->title }}</a>
                        </h3>

                        @if (filled($blog->excerpt))
                            <p class="text-zinc-600 dark:text-zinc-300">{{ $blog->excerpt }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <flux:pagination :paginator="$this->blogs" />
    @endif
</div>
