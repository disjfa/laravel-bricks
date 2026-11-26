<?php

namespace App\Models;

use App\Bricks\Brickable;
use App\Concerns\HasBricks;
use App\Concerns\HasPublishing;
use App\Concerns\HasSeo;
use App\Enums\BlogTagType;
use App\Enums\PublishStatus;
use App\Settings\SeoSettings;
use Carbon\CarbonInterface;
use Database\Factories\BlogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;
use Spatie\Tags\Tag;

/**
 * A blog is built from bricks like a page and is shown at /blog/{slug}. It has categories and tags
 * (spatie/laravel-tags, see BlogTagType).
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $image_url
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_image
 * @property bool $noindex
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $published_until
 * @property int $seqnr
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Brick> $bricks
 * @property-read Collection<int, Media> $media
 * @property-read Collection<int, Tag> $tags
 */
#[Fillable(['title', 'slug', 'excerpt', 'image_url', 'meta_title', 'meta_description', 'meta_image', 'noindex', 'status', 'published_at', 'published_until', 'seqnr'])]
class Blog extends Model implements Brickable
{
    use HasBricks;

    /** @use HasFactory<BlogFactory> */
    use HasFactory;

    use HasPublishing;
    use HasSeo;
    use HasSlug;
    use HasTags;
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MediaCollection);
    }

    /**
     * The thumbnail goes over the queue: with the sync queue it is made during the upload, with a real
     * queue (database, Horizon) a worker makes it. Until then the admin shows the original image.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 300);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * The date shown to readers: the publish date, or the creation date when there is none.
     */
    public function displayDate(): ?CarbonInterface
    {
        return $this->published_at ?? $this->created_at;
    }

    public function adminSection(): string
    {
        return 'blogs';
    }

    public function publicPath(): string
    {
        return '/blog/'.$this->slug;
    }

    public function url(): string
    {
        return route('blogs.show', ['blog' => $this->slug]);
    }

    /**
     * Blogs without a meta description use their excerpt.
     */
    public function seoDescription(): ?string
    {
        return collect([$this->meta_description, $this->excerpt, app(SeoSettings::class)->description()])->first(fn (?string $description) => filled($description));
    }

    protected function fallbackSeoImage(): ?string
    {
        return $this->image_url;
    }

    protected function seoType(): string
    {
        return 'article';
    }

    /**
     * The categories or the tags of the blog, sorted by name.
     *
     * @return Collection<int, Tag>
     */
    public function tagsOfType(BlogTagType $type): Collection
    {
        return $this->tagsWithType($type->value)
            ->whereInstanceOf(Tag::class)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }
}
