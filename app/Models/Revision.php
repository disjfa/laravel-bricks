<?php

namespace App\Models;

use App\Enums\RevisionAction;
use App\Revisions\Revisionable;
use Database\Factories\RevisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * A full snapshot of a page, blog or form (App\Revisions\Revisionable) after one of its saves.
 *
 * @property int $id
 * @property string $revisionable_type
 * @property int $revisionable_id
 * @property int|null $user_id
 * @property RevisionAction $action
 * @property int|null $reverted_from_id
 * @property array<string, mixed> $snapshot
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read (Model&Revisionable)|null $revisionable
 * @property-read User|null $user
 * @property-read Revision|null $revertedFrom
 */
#[Fillable(['user_id', 'action', 'reverted_from_id', 'snapshot'])]
class Revision extends Model
{
    /** @use HasFactory<RevisionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => RevisionAction::class,
            'snapshot' => 'array',
        ];
    }

    /**
     * The page, blog or form, or null when it was deleted.
     *
     * @return MorphTo<Model, $this>
     */
    public function revisionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The revision a revert went back to.
     *
     * @return BelongsTo<Revision, $this>
     */
    public function revertedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverted_from_id');
    }
}
