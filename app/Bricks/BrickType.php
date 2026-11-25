<?php

namespace App\Bricks;

use App\Bricks\Fields\Field;
use Illuminate\Validation\Validator;

/**
 * A kind of brick a page can be built from. Every class in app/Bricks that implements
 * this interface is discovered automatically, see BrickServiceProvider.
 */
interface BrickType
{
    /**
     * The identifier stored in the bricks.type column.
     */
    public function key(): string;

    public function label(): string;

    /**
     * A Heroicon name.
     */
    public function icon(): string;

    /**
     * Position in the "Add brick" menu, lower comes first.
     */
    public function order(): int;

    /**
     * The fields an editor fills in. They define the data the website view receives.
     *
     * @return array<int, Field>
     */
    public function fields(): array;

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array;

    /**
     * @param  array<string, mixed>  $data
     */
    public function validator(array $data): Validator;

    /**
     * The Blade view that renders the brick on the website. It receives the validated $data.
     */
    public function view(): string;
}
