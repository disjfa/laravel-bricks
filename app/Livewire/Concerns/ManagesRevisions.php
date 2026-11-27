<?php

namespace App\Livewire\Concerns;

use App\Models\Revision;
use App\Revisions\Revisionable;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

/**
 * The Revisions tab of a page, blog or form: the list of revisions, a preview of the chosen one and a revert.
 * Render the list with the partials.admin.revision-list view.
 */
trait ManagesRevisions
{
    use WithPagination;

    /**
     * The revision in the preview, or null for the newest one.
     */
    public ?int $selectedRevisionId = null;

    abstract protected function revisionable(): Model&Revisionable;

    /**
     * @return LengthAwarePaginator<int, Revision>
     */
    #[Computed]
    public function revisions(): LengthAwarePaginator
    {
        return $this->revisionable()->revisions()->with(['user', 'revertedFrom'])->paginate(20, pageName: 'revisions');
    }

    /**
     * The newest revision: the version that is saved now.
     */
    #[Computed]
    public function currentRevisionId(): ?int
    {
        return $this->revisionable()->revisions()->value('id');
    }

    #[Computed]
    public function selectedRevision(): ?Revision
    {
        $revisions = $this->revisionable()->revisions();

        return $this->selectedRevisionId === null
            ? $revisions->first()
            : $revisions->whereKey($this->selectedRevisionId)->first();
    }

    /**
     * The children of the chosen revision (like its bricks or fields), with the key the preview components need.
     *
     * @return array<int, array<string, mixed>>
     */
    public function previewChildren(string $name): array
    {
        $revision = $this->selectedRevision();
        $children = $revision?->snapshot[$name] ?? [];

        if (! is_array($children)) {
            return [];
        }

        $previewChildren = [];

        foreach (array_values($children) as $index => $child) {
            if (is_array($child)) {
                $previewChildren[] = [...$child, 'key' => "revision-{$revision?->id}-{$index}"];
            }
        }

        return $previewChildren;
    }

    public function selectRevision(int $revisionId): void
    {
        $this->selectedRevisionId = $this->revisionable()->revisions()->whereKey($revisionId)->value('id');

        unset($this->selectedRevision);
    }

    public function revert(int $revisionId): void
    {
        $revisionable = $this->revisionable();

        $this->authorize('update', $revisionable);

        $revision = $revisionable->revisions()->findOrFail($revisionId);

        $revisionable->revertTo($revision);

        $this->selectedRevisionId = null;
        $this->resetPage('revisions');
        unset($this->revisions, $this->currentRevisionId, $this->selectedRevision);

        Flux::toast(variant: 'success', text: __('Reverted to the version of :date.', ['date' => $revision->created_at?->isoFormat('LLL')]));
    }
}
