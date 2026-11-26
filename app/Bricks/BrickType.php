<?php

namespace App\Bricks;

use App\Fields\Field;
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
     * Whether the brick can be added and is shown on the website, false when its feature is turned off.
     */
    public function isAvailable(): bool;

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
     * Settings that change how the brick is shown rather than what it shows, like its width.
     * They are edited in the brick settings modal.
     *
     * @return array<int, Field>
     */
    public function settings(): array;

    /**
     * @return array<string, mixed>
     */
    public function settingsDefaults(): array;

    /**
     * @param  array<string, mixed>  $settings
     */
    public function settingsValidator(array $settings): Validator;

    /**
     * The Blade view that renders the brick on the website. It receives the validated $data and $settings.
     */
    public function view(): string;
}
