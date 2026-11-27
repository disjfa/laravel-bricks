<?php

namespace App\Revisions;

use App\Enums\RevisionAction;
use App\Models\Revision;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A model with revisions (pages, blogs and forms): a full snapshot after every save, which it can go back to.
 * Implemented with App\Concerns\HasRevisions; the model itself says what its snapshot holds and how to restore it.
 *
 * @mixin Model
 */
interface Revisionable
{
    /**
     * The model and its children as they are now, like a page with its bricks.
     */
    public function snapshotResource(): JsonResource;

    /**
     * Put the model and its children back as they were in a snapshot made by snapshotResource().
     *
     * @param  array<string, mixed>  $snapshot
     */
    public function restoreSnapshot(array $snapshot): void;

    /**
     * @return MorphMany<Revision, covariant Model>
     */
    public function revisions(): MorphMany;

    /**
     * Save changes and record a revision of the result.
     *
     * @param  Closure(): void  $changes
     */
    public function recordingRevision(Closure $changes): void;

    public function recordRevision(RevisionAction $action = RevisionAction::Updated, ?Revision $revertedFrom = null): ?Revision;

    /**
     * Delete the model and keep its last state as a revision.
     */
    public function deleteWithRevision(): void;

    /**
     * Go back to an earlier revision, which is recorded as a new revision.
     */
    public function revertTo(Revision $revision): Revision;
}
