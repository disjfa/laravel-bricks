<?php

namespace App\Registry;

/**
 * Something a TypeRegistry holds, like a brick type or a form field type.
 */
interface RegisteredType
{
    /**
     * The identifier that is stored, like the bricks.type column.
     */
    public function key(): string;

    public function label(): string;

    /**
     * A Heroicon name.
     */
    public function icon(): string;

    /**
     * Position in the "Add" menu, lower comes first.
     */
    public function order(): int;

    /**
     * Whether it can be added right now, false when its feature is turned off.
     */
    public function isAvailable(): bool;
}
