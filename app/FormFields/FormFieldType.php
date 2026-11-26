<?php

namespace App\FormFields;

use App\Fields\Field;
use App\Registry\RegisteredType;
use Illuminate\Validation\Validator;

/**
 * A kind of field a form can be built from. Every class in app/FormFields that implements
 * this interface is discovered automatically, like the brick types, see BrickServiceProvider.
 */
interface FormFieldType extends RegisteredType
{
    /**
     * The settings an editor fills in for the field, like its label or options.
     *
     * @return array<int, Field>
     */
    public function settings(): array;

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array;

    /**
     * Validates the settings an editor entered.
     *
     * @param  array<string, mixed>  $settings
     */
    public function validator(array $settings): Validator;

    /**
     * The rules for the value a visitor submits.
     *
     * @param  array<string, mixed>  $settings
     * @return array<int, mixed>
     */
    public function rules(array $settings): array;

    /**
     * The value a visitor starts with.
     */
    public function emptyValue(): mixed;

    /**
     * The Blade view that renders the field on the website. It receives $type, $settings and $model (the wire:model path).
     */
    public function view(): string;
}
