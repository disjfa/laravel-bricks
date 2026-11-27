<?php

namespace App\Http\Resources\V1;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MenuItem
 */
class MenuItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'label' => $this->label,
            /** The link type, like "page", "blog" or "url". */
            'type' => $this->type,
            'url' => $this->url(),
            'new_tab' => $this->opensInNewTab(),
        ];
    }
}
