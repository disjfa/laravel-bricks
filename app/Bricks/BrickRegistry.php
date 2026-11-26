<?php

namespace App\Bricks;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Container\Attributes\Tag;

/**
 * Holds every available brick type, keyed by its key and sorted for the "Add brick" menu.
 * The container injects everything tagged as a BrickType, see BrickServiceProvider.
 */
#[Singleton]
class BrickRegistry
{
    /**
     * @var array<string, BrickType>
     */
    protected array $types = [];

    /**
     * @param  iterable<BrickType>  $types
     */
    public function __construct(#[Tag(BrickType::class)] iterable $types)
    {
        $types = collect($types)
            ->sortBy(fn (BrickType $type) => [$type->order(), $type->label()])
            ->all();

        foreach ($types as $type) {
            $this->types[$type->key()] = $type;
        }
    }

    /**
     * @return array<string, BrickType>
     */
    public function all(): array
    {
        return $this->types;
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->types);
    }

    /**
     * The brick types that can be added right now.
     *
     * @return array<string, BrickType>
     */
    public function available(): array
    {
        return array_filter($this->types, fn (BrickType $type) => $type->isAvailable());
    }

    public function find(string $key): ?BrickType
    {
        return $this->types[$key] ?? null;
    }
}
