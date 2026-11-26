<?php

namespace App\Http\Resources\V1;

use App\Enums\BlogTagType;
use App\Models\Blog;
use App\Models\Brick;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Blog
 */
class BlogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'url' => $this->url(),
            'excerpt' => $this->excerpt,
            'image' => filled($this->image_url) ? url($this->image_url) : null,
            'categories' => TagResource::collection($this->tagsOfType(BlogTagType::Category)),
            'tags' => TagResource::collection($this->tagsOfType(BlogTagType::Tag)),
            'seo' => [
                'title' => $this->seoTitle(),
                'description' => $this->seoDescription(),
                'image' => $this->seoImage(),
                'noindex' => ! $this->isIndexable(),
            ],
            'seqnr' => $this->seqnr,
            'published_at' => $this->published_at?->toIso8601String(),
            'published_until' => $this->published_until?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            /** The bricks the blog is built from, in order. Only with ?include=bricks on the list. */
            'bricks' => BrickResource::collection($this->whenLoaded(
                'bricks',
                fn () => $this->bricks->filter(fn (Brick $brick) => $brick->renderableDefinition() !== null)->values(),
            )),
        ];
    }
}
