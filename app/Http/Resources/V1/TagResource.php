<?php

namespace App\Http\Resources\V1;

use App\Enums\BlogTagType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Tags\Tag;

/**
 * A category or tag of a blog.
 *
 * @mixin Tag
 */
class TagResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = BlogTagType::tryFrom((string) $this->type);

        return [
            'name' => $this->name,
            'slug' => $this->slug,
            /** The archive page with the blogs of this category or tag. */
            'url' => $type?->url($this->resource),
        ];
    }
}
