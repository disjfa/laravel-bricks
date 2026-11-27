<?php

namespace App\MenuLinks;

use App\Registry\TypeRegistry;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Container\Attributes\Tag;

/**
 * Every menu link type in app/MenuLinks, see BrickServiceProvider.
 *
 * @extends TypeRegistry<MenuLinkType>
 */
#[Singleton]
class MenuLinkRegistry extends TypeRegistry
{
    /**
     * @param  iterable<MenuLinkType>  $types
     */
    public function __construct(#[Tag(MenuLinkType::class)] iterable $types)
    {
        parent::__construct($types);
    }
}
