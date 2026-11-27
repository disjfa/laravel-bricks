<?php

namespace App\MenuLinks;

use App\Fields\Link;
use App\Fields\Toggle;

/**
 * Any address: a path on the website, an anchor, a mail link or another website.
 */
class UrlLink extends BaseMenuLink
{
    public function key(): string
    {
        return 'url';
    }

    public function label(): string
    {
        return __('Custom link');
    }

    public function icon(): string
    {
        return 'link';
    }

    public function order(): int
    {
        return 30;
    }

    public function fields(): array
    {
        return [
            Link::make('url')->label(__('Link'))->required()->placeholder('/contact'),
            Toggle::make('new_tab')->label(__('Open in a new tab')),
        ];
    }

    public function url(array $data): ?string
    {
        return filled($data['url'] ?? null) ? $data['url'] : null;
    }

    public function opensInNewTab(array $data): bool
    {
        return (bool) ($data['new_tab'] ?? false);
    }
}
