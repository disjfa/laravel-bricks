<?php

namespace App\MenuLinks;

use App\Fields\Field;
use App\Registry\RegisteredType;
use Illuminate\Validation\Validator;

/**
 * A kind of item a menu can link to, like a page or a custom link. Every class in app/MenuLinks that
 * implements this interface is discovered automatically, like the brick types, see BrickServiceProvider.
 */
interface MenuLinkType extends RegisteredType
{
    /**
     * The fields an editor fills in, like the page to link to. They define the data of the menu item.
     *
     * @return array<int, Field>
     */
    public function fields(): array;

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array;

    /**
     * @param  array<string, mixed>  $data
     */
    public function validator(array $data): Validator;

    /**
     * A label for a new item, like the title of the picked page, or null to leave it to the editor.
     *
     * @param  array<string, mixed>  $data
     */
    public function suggestedLabel(array $data): ?string;

    /**
     * Where the item links to, or null when it should not be shown, like a page that is not published.
     *
     * @param  array<string, mixed>  $data
     */
    public function url(array $data): ?string;

    /**
     * @param  array<string, mixed>  $data
     */
    public function opensInNewTab(array $data): bool;
}
