<?php

namespace App\Bricks;

use App\Registry\TypeRegistry;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Container\Attributes\Tag;

/**
 * Every brick type in app/Bricks, see BrickServiceProvider.
 *
 * @extends TypeRegistry<BrickType>
 */
#[Singleton]
class BrickRegistry extends TypeRegistry
{
    /**
     * @param  iterable<BrickType>  $types
     */
    public function __construct(#[Tag(BrickType::class)] iterable $types)
    {
        parent::__construct($types);
    }
}
