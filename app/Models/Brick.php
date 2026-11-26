<?php

namespace App\Models;

use App\Bricks\Brickable;
use App\Bricks\BrickRegistry;
use App\Bricks\BrickType;
use Database\Factories\BrickFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $brickable_type
 * @property int $brickable_id
 * @property string $type
 * @property array<string, mixed> $data
 * @property array<string, mixed>|null $settings
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model&Brickable $brickable
 */
#[Fillable(['type', 'data', 'settings', 'position'])]
class Brick extends Model
{
    /** @use HasFactory<BrickFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'settings' => 'array',
            'position' => 'integer',
        ];
    }

    /**
     * The brick type this brick is built from, or null when that type no longer exists.
     */
    public function definition(): ?BrickType
    {
        return app(BrickRegistry::class)->find($this->type);
    }

    /**
     * The brick type when it may be shown on the website, null when it no longer exists or its feature is off.
     */
    public function renderableDefinition(): ?BrickType
    {
        $definition = $this->definition();

        return $definition?->isAvailable() ? $definition : null;
    }

    /**
     * The page or blog the brick is part of.
     *
     * @return MorphTo<Model, $this>
     */
    public function brickable(): MorphTo
    {
        return $this->morphTo();
    }
}
