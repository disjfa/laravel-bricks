<?php

namespace App\Concerns;

use App\Enums\RevisionAction;
use App\Models\Revision;
use Closure;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * The revisions of an App\Revisions\Revisionable model. The model says what a snapshot holds with
 * snapshotResource() and how to put one back with restoreSnapshot().
 *
 * Wrap every save of the model and its children in recordingRevision(): the first time it also records the
 * state from before the change, so the very first version can be restored too.
 */
trait HasRevisions
{
    /**
     * Newest first.
     *
     * @return MorphMany<Revision, $this>
     */
    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable')->latest('id');
    }

    /**
     * @param  Closure(): void  $changes
     */
    public function recordingRevision(Closure $changes): void
    {
        DB::transaction(function () use ($changes): void {
            $this->recordFirstRevision();

            $changes();

            $this->recordRevision();
        });
    }

    /**
     * Record the current state. An update that did not change anything is not recorded.
     */
    public function recordRevision(RevisionAction $action = RevisionAction::Updated, ?Revision $revertedFrom = null): ?Revision
    {
        $snapshot = $this->snapshot();

        // Compared as arrays: a JSON column may store the keys in another order.
        if ($action === RevisionAction::Updated && $this->revisions()->first()?->snapshot == $snapshot) {
            return null;
        }

        return $this->revisions()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'reverted_from_id' => $revertedFrom?->id,
            'snapshot' => $snapshot,
        ]);
    }

    /**
     * Delete the model and keep its last state as a revision, so it can be found and restored later.
     */
    public function deleteWithRevision(): void
    {
        DB::transaction(function (): void {
            $this->recordRevision(RevisionAction::Deleted);

            $this->delete();
        });
    }

    public function revertTo(Revision $revision): Revision
    {
        if ($revision->revisionable_type !== $this->getMorphClass() || $revision->revisionable_id !== $this->getKey()) {
            throw new InvalidArgumentException('The revision belongs to another model.');
        }

        return DB::transaction(function () use ($revision): Revision {
            $this->recordFirstRevision();

            $this->restoreSnapshot($revision->snapshot);

            return $this->recordRevision(RevisionAction::Reverted, $revision);
        });
    }

    /**
     * The snapshot as it is stored: the resource as plain JSON data. It is made from the model as it is in the
     * database, so defaults of the database (like noindex) are in it too.
     *
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        $model = $this->exists ? ($this->fresh() ?? $this) : $this;

        return json_decode(json_encode($model->snapshotResource()->resolve(), JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * The slug from a snapshot, or the current one when another record took it in the meantime.
     */
    protected function restorableSlug(string $slug): string
    {
        $taken = static::query()->where('slug', $slug)->whereKeyNot($this->getKey())->exists();

        return $taken ? $this->getAttribute('slug') : $slug;
    }

    /**
     * Models from before revisions existed, or made without the editor, get their current state as the first revision.
     */
    protected function recordFirstRevision(): void
    {
        if (! $this->revisions()->exists()) {
            $this->recordRevision(RevisionAction::Created);
        }
    }
}
