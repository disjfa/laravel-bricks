<?php

namespace App\Http\Resources\Snapshots;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A page with its bricks in a revision snapshot: the fields as they are stored, without the SEO defaults.
 * The sequence number is left out, a revert does not change the order of the pages.
 *
 * @mixin Page
 */
class PageSnapshot extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_image' => $this->meta_image,
            'noindex' => $this->noindex,
            'status' => $this->status->value,
            'published_at' => $this->published_at?->toIso8601String(),
            'published_until' => $this->published_until?->toIso8601String(),
            'bricks' => BrickSnapshot::collection($this->bricks()->get()),
        ];
    }
}
