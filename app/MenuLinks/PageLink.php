<?php

namespace App\MenuLinks;

use App\Fields\Select;
use App\Models\Page;

class PageLink extends BaseMenuLink
{
    public function key(): string
    {
        return 'page';
    }

    public function label(): string
    {
        return __('Page');
    }

    public function icon(): string
    {
        return 'document-text';
    }

    public function order(): int
    {
        return 10;
    }

    public function fields(): array
    {
        return [
            Select::make('page_id')
                ->label(__('Page'))
                ->required()
                ->options(Page::query()->ordered()->pluck('title', 'id')->all()),
        ];
    }

    public function suggestedLabel(array $data): ?string
    {
        return $this->page($data)?->title;
    }

    /**
     * Only published pages are linked. The page with the "home" slug links to the front page.
     */
    public function url(array $data): ?string
    {
        $page = $this->page($data);

        if (! $page?->isPublished()) {
            return null;
        }

        return $page->slug === 'home' ? route('home') : $page->url();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function page(array $data): ?Page
    {
        $id = $data['page_id'] ?? null;

        return is_numeric($id) ? Page::query()->find((int) $id) : null;
    }
}
