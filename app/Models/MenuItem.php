<?php

namespace App\Models;

use App\MenuLinks\MenuLinkRegistry;
use App\MenuLinks\MenuLinkType;
use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One link of a menu. Like a brick, its type (App\MenuLinks) decides what it links to and which fields it has.
 *
 * @property int $id
 * @property int $menu_id
 * @property string $label
 * @property string $type
 * @property array<string, mixed> $data
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Menu $menu
 */
#[Fillable(['label', 'type', 'data', 'position'])]
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
            'data' => 'array',
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
     * The link type, or null when that type no longer exists.
     */
    public function definition(): ?MenuLinkType
    {
        return app(MenuLinkRegistry::class)->find($this->type);
    }

    /**
     * Where the item links to, or null when it should not be shown: its type no longer exists or is turned off,
     * or it points to something that is not published.
     */
    public function url(): ?string
    {
        $definition = $this->definition();

        return $definition?->isAvailable() ? $definition->url($this->data) : null;
    }

    public function opensInNewTab(): bool
    {
        return $this->definition()?->opensInNewTab($this->data) ?? false;
    }
}
