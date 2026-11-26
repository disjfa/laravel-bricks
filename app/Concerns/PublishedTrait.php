<?php

namespace App\Concerns;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Publishing for models with the columns status, published_at, published_until and seqnr.
 *
 * A model is visible on the website when its status is active and "now" falls between the
 * publish date and the publish until date (both optional). The seqnr orders the records of
 * one model and is filled with the next number when a record is created.
 *
 * @property PublishStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $published_until
 * @property int $seqnr
 */
trait PublishedTrait
{
    public static function bootPublishedTrait(): void
    {
        static::creating(function (Model $model): void {
            $model->status ??= PublishStatus::Concept;

            if ($model->seqnr === null) {
                $model->seqnr = (int) static::query()->max('seqnr') + 1;
            }
        });
    }

    public function initializePublishedTrait(): void
    {
        $this->mergeCasts([
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'published_until' => 'datetime',
            'seqnr' => 'integer',
        ]);
    }

    /**
     * Records that are visible on the website right now.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $table = $this->getTable();

        $query
            ->where("{$table}.status", PublishStatus::Active)
            ->where(fn (Builder $query) => $query
                ->whereNull("{$table}.published_at")
                ->orWhere("{$table}.published_at", '<=', now()))
            ->where(fn (Builder $query) => $query
                ->whereNull("{$table}.published_until")
                ->orWhere("{$table}.published_until", '>', now()));
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query
            ->orderBy($this->qualifyColumn('seqnr'))
            ->orderBy($this->qualifyColumn($this->getKeyName()));
    }

    public function isPublished(): bool
    {
        return $this->status === PublishStatus::Active
            && ! $this->isScheduled()
            && ! $this->isExpired();
    }

    /**
     * Active, but the publish date is still to come.
     */
    public function isScheduled(): bool
    {
        return $this->status === PublishStatus::Active
            && $this->published_at !== null
            && $this->published_at->isFuture();
    }

    /**
     * Active, but the publish until date has passed.
     */
    public function isExpired(): bool
    {
        return $this->status === PublishStatus::Active
            && $this->published_until !== null
            && ! $this->published_until->isFuture();
    }

    public function publicationLabel(): string
    {
        return match (true) {
            $this->isScheduled() => __('Scheduled'),
            $this->isExpired() => __('Expired'),
            $this->isPublished() => __('Published'),
            default => ($this->status ?? PublishStatus::Concept)->label(),
        };
    }

    /**
     * A Flux badge color for the publication state.
     */
    public function publicationColor(): string
    {
        return match (true) {
            $this->isScheduled() => 'amber',
            $this->isExpired() => 'orange',
            $this->isPublished() => 'green',
            $this->status === PublishStatus::Closed => 'red',
            default => 'zinc',
        };
    }
}
