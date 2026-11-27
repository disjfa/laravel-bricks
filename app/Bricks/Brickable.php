<?php

namespace App\Bricks;

use App\Models\Brick;
use App\Revisions\Revisionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;

/**
 * A model that is built from bricks in the admin (pages and blogs), with its own media panel and revisions.
 * Implemented with App\Concerns\HasBricks and App\Concerns\HasRevisions.
 *
 * @mixin Model
 */
interface Brickable extends HasMedia, Revisionable
{
    public const string MediaCollection = 'media';

    /**
     * @return MorphMany<Brick, covariant Model>
     */
    public function bricks(): MorphMany;

    /**
     * The admin route group, like "pages" for admin.pages.edit.
     */
    public function adminSection(): string;

    /**
     * The path on the website, like "/about".
     */
    public function publicPath(): string;

    public function url(): string;

    /**
     * Images of the media panel, offered in the image pickers of the editor.
     *
     * @return array<int, array{id: int, name: string, url: string, thumb: string}>
     */
    public function imageMediaForPicker(): array;
}
