<?php

namespace App\Concerns;

use App\Bricks\Brickable;
use App\Models\Brick;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * The bricks and media panel of a Brickable model.
 */
trait HasBricks
{
    public static function bootHasBricks(): void
    {
        static::deleting(fn (self $model) => $model->bricks()->delete());
    }

    /**
     * @return MorphMany<Brick, $this>
     */
    public function bricks(): MorphMany
    {
        return $this->morphMany(Brick::class, 'brickable')->orderBy('position');
    }

    /**
     * @return array<int, array{id: int, name: string, url: string, thumb: string}>
     */
    public function imageMediaForPicker(): array
    {
        return $this->media()
            ->where('collection_name', Brickable::MediaCollection)
            ->where('mime_type', 'like', 'image/%')
            ->latest('id')
            ->get()
            ->map(fn (Media $media) => [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->getUrl(),
                'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
            ])
            ->all();
    }
}
