<?php

namespace App\Models;

use App\Bricks\BrickRegistry;
use App\Bricks\BrickType;
use Database\Factories\BrickFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $page_id
 * @property string $type
 * @property array<string, mixed> $data
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Page $page
 */
#[Fillable(['type', 'data', 'position'])]
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
     * @return BelongsTo<Page, $this>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
