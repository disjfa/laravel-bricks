<?php

namespace App\Fields;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

/**
 * A list of fields, like the fields of a brick or the settings of a form field, turned into
 * defaults and validation.
 */
final class FieldSet
{
    /**
     * @param  array<int, Field>  $fields
     */
    public function __construct(public readonly array $fields) {}

    /**
     * @param  array<int, Field>  $fields
     */
    public static function of(array $fields): self
    {
        return new self($fields);
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn (Field $field) => [$field->name => $field->getDefault()])
            ->all();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return collect($this->fields)
            ->mapWithKeys(fn (Field $field) => [$field->name => $field->getRules()])
            ->all();
    }

    /**
     * Validates the data, with the field labels in the validation messages.
     *
     * @param  array<string, mixed>  $data
     */
    public function validator(array $data): Validator
    {
        return ValidatorFacade::make(
            $data,
            $this->rules(),
            attributes: collect($this->fields)
                ->mapWithKeys(fn (Field $field) => [$field->name => mb_strtolower($field->getLabel())])
                ->all(),
        );
    }
}
