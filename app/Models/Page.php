<?php

namespace App\Models;

use App\Bricks\Brickable;
use App\Concerns\HasBricks;
use App\Concerns\HasPublishing;
use App\Concerns\HasSeo;
use App\Enums\PublishStatus;
use App\Settings\SeoSettings;
use Database\Factories\PageFactory;
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

/**
 * @property int $id
 * @property string $title
 * @property string $slug
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
 */
#[Fillable(['title', 'slug', 'meta_title', 'meta_description', 'meta_image', 'noindex', 'status', 'published_at', 'published_until', 'seqnr'])]
class Page extends Model implements Brickable
{
    use HasBricks;

    /** @use HasFactory<PageFactory> */
    use HasFactory;

    use HasPublishing;
    use HasSeo;
    use HasSlug;
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MediaCollection);
    }

    /**
     * The thumbnail is generated right away so the media panel can show it after uploading.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->nonQueued()
            ->fit(Fit::Crop, 400, 300);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function adminSection(): string
    {
        return 'pages';
    }

    public function publicPath(): string
    {
        return '/'.$this->slug;
    }

    public function url(): string
    {
        return url($this->slug);
    }

    /**
     * The home page uses the default meta title as is.
     */
    public function seoTitle(): string
    {
        if (blank($this->meta_title) && $this->slug === 'home') {
            return app(SeoSettings::class)->title();
        }

        return filled($this->meta_title) ? $this->meta_title : $this->title.' - '.app(SeoSettings::class)->title();
    }
}
