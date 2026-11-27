<?php

namespace App\Http\Resources\Snapshots;

use App\Enums\BlogTagType;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A blog with its bricks, categories and tags in a revision snapshot: the fields as they are stored, without the
 * SEO defaults. The sequence number is left out, a revert does not change the order of the blogs.
 *
 * @mixin Blog
 */
class BlogSnapshot extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->load('tags');

        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'image_url' => $this->image_url,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_image' => $this->meta_image,
            'noindex' => $this->noindex,
            'status' => $this->status->value,
            'published_at' => $this->published_at?->toIso8601String(),
            'published_until' => $this->published_until?->toIso8601String(),
            'categories' => $this->tagsOfType(BlogTagType::Category)->pluck('name')->all(),
            'tags' => $this->tagsOfType(BlogTagType::Tag)->pluck('name')->all(),
            'bricks' => BrickSnapshot::collection($this->bricks()->get()),
        ];
    }
}
