<?php

namespace App\Http\Resources\V1;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Menu
 */
class MenuResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            /** The website shows "main" in the header and "footer" in the footer. */
            'handle' => $this->handle,
            /** The items that link somewhere visible, in order. Only with ?include=items on the list. */
            'items' => MenuItemResource::collection($this->whenLoaded(
                'items',
                fn () => $this->items->filter(fn (MenuItem $item) => $item->url() !== null)->values(),
            )),
        ];
    }
}
