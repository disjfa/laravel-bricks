<?php

namespace App\Http\Resources\Snapshots;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A form with its fields in a revision snapshot. The submissions are not part of it.
 *
 * @mixin Form
 */
class FormSnapshot extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'submit_label' => $this->submit_label,
            'success_message' => $this->success_message,
            'fields' => FormFieldSnapshot::collection($this->fields()->get()),
        ];
    }
}
