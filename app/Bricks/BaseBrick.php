<?php

namespace App\Bricks;

use App\Bricks\Fields\Field;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

/**
 * Derives the defaults, validation and view of a brick from its fields.
 */
abstract class BaseBrick implements BrickType
{
    public function order(): int
    {
        return 100;
    }

    public function defaults(): array
    {
        return collect($this->fields())
            ->mapWithKeys(fn (Field $field) => [$field->name => $field->getDefault()])
            ->all();
    }

    public function rules(): array
    {
        return collect($this->fields())
            ->mapWithKeys(fn (Field $field) => [$field->name => $field->getRules()])
            ->all();
    }

    public function validator(array $data): Validator
    {
        return ValidatorFacade::make($data, $this->rules(), attributes: $this->validationAttributes());
    }

    public function view(): string
    {
        return "bricks.web.{$this->key()}";
    }

    /**
     * Use the field labels in validation messages.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return collect($this->fields())
            ->mapWithKeys(fn (Field $field) => [$field->name => mb_strtolower($field->getLabel())])
            ->all();
    }
}
