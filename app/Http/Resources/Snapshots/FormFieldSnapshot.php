<?php

namespace App\Http\Resources\Snapshots;

use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A form field in a revision snapshot.
 *
 * @mixin FormField
 */
class FormFieldSnapshot extends JsonResource
{
    /**
     * @return array{type: string, name: string, settings: array<string, mixed>}
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'settings' => $this->settings,
        ];
    }
}
