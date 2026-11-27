<?php

namespace App\Http\Resources\V1;

use App\Models\Brick;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Brick
 */
class BrickResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            /** The brick type, like "hero" or "text". */
            'type' => $this->type,
            /** The content of the brick, its fields depend on the type. */
            'data' => $this->data,
            /** How the brick is shown, like its width. */
            'settings' => array_merge($this->definition()?->settingsDefaults() ?? [], $this->settings ?? []),
            'position' => $this->position,
        ];
    }
}
