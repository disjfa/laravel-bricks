<?php

namespace App\MenuLinks;

use App\Features\Blogs;
use App\Fields\Select;
use App\Models\Blog;
use Laravel\Pennant\Feature;

class BlogLink extends BaseMenuLink
{
    public function key(): string
    {
        return 'blog';
    }

    public function label(): string
    {
        return __('Blog');
    }

    public function icon(): string
    {
        return 'newspaper';
    }

    public function order(): int
    {
        return 20;
    }

    public function isAvailable(): bool
    {
        return Feature::active(Blogs::class);
    }

    public function fields(): array
    {
        return [
            Select::make('blog_id')
                ->label(__('Blog'))
                ->required()
                ->options(Blog::query()->latest('published_at')->latest('id')->pluck('title', 'id')->all()),
        ];
    }

    public function suggestedLabel(array $data): ?string
    {
        return $this->blog($data)?->title;
    }

    /**
     * Only published blogs are linked.
     */
    public function url(array $data): ?string
    {
        $blog = $this->blog($data);

        return $blog?->isPublished() ? $blog->url() : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function blog(array $data): ?Blog
    {
        $id = $data['blog_id'] ?? null;

        return is_numeric($id) ? Blog::query()->find((int) $id) : null;
    }
}
