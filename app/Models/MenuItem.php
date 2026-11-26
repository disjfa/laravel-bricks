<?php

namespace App\Models;

use App\Enums\MenuItemType;
use App\Features\Blogs;
use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Laravel\Pennant\Feature;

/**
 * @property int $id
 * @property int $menu_id
 * @property string $label
 * @property MenuItemType $type
 * @property int|null $page_id
 * @property int|null $blog_id
 * @property string|null $url
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Menu $menu
 * @property-read Page|null $page
 * @property-read Blog|null $blog
 */
#[Fillable(['label', 'type', 'page_id', 'blog_id', 'url', 'position'])]
class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MenuItemType::class,
            'position' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * @return BelongsTo<Blog, $this>
     */
    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * Where the item links to, or null when it points to something that is not published
     * (or to a blog while blogs are turned off).
     */
    public function url(): ?string
    {
        return match ($this->type) {
            MenuItemType::Page => $this->page?->isPublished() ? $this->pageUrl($this->page) : null,
            MenuItemType::Blog => Feature::active(Blogs::class) && $this->blog?->isPublished() ? $this->blog->url() : null,
            MenuItemType::Url => $this->url,
        };
    }

    protected function pageUrl(Page $page): string
    {
        return $page->slug === 'home' ? route('home') : $page->url();
    }
}
