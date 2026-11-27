<?php

namespace App\Http\Resources\Snapshots;

use App\Models\Brick;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A brick in a revision snapshot, as it is stored: no defaults are filled in.
 *
 * @mixin Brick
 */
class BrickSnapshot extends JsonResource
{
    /**
     * @return array{type: string, data: array<string, mixed>, settings: array<string, mixed>|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'settings' => $this->settings,
        ];
    }
}
