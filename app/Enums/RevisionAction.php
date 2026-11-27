<?php

namespace App\Enums;

/**
 * What happened to a page, blog or form when its revision was made.
 */
enum RevisionAction: string
{
    /** The first revision: the state before its first change was saved. */
    case Created = 'created';
    case Updated = 'updated';
    case Reverted = 'reverted';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Created => __('First version'),
            self::Updated => __('Saved'),
            self::Reverted => __('Reverted'),
            self::Deleted => __('Deleted'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Created => 'zinc',
            self::Updated => 'blue',
            self::Reverted => 'amber',
            self::Deleted => 'red',
        };
    }
}
