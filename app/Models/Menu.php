<?php

namespace App\Models;

use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A navigation menu built in the admin. The website shows the menus with the handles below.
 *
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, MenuItem> $items
 */
#[Fillable(['name', 'handle'])]
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    public const string MainHandle = 'main';

    public const string FooterHandle = 'footer';

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('position');
    }

    /**
     * The items of the menu with the given handle that link somewhere visible.
     *
     * @return Collection<int, MenuItem>
     */
    public static function visibleItems(string $handle): Collection
    {
        $menu = static::query()
            ->where('handle', $handle)
            ->with('items')
            ->first();

        if (! $menu) {
            return new Collection;
        }

        return $menu->items
            ->filter(fn (MenuItem $item) => $item->url() !== null)
            ->values();
    }
}
