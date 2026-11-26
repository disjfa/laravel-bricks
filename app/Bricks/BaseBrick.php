<?php

namespace App\Bricks;

use App\Enums\BrickWidth;
use App\Fields\Field;
use App\Fields\Select;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

/**
 * Derives the defaults, validation and view of a brick from its fields, and gives every brick
 * the same settings (like its width). Override extraSettings() to give a brick settings of its own.
 */
abstract class BaseBrick implements BrickType
{
    public function order(): int
    {
        return 100;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function defaults(): array
    {
        return $this->defaultsFor($this->fields());
    }

    public function rules(): array
    {
        return $this->rulesFor($this->fields());
    }

    public function validator(array $data): Validator
    {
        return $this->validatorFor($this->fields(), $data);
    }

    /**
     * The settings every brick has, like its width. A brick adds its own with extraSettings().
     */
    public function settings(): array
    {
        return [
            Select::make('width')
                ->label(__('Width'))
                ->required()
                ->options(collect(BrickWidth::cases())->mapWithKeys(fn (BrickWidth $width) => [$width->value => $width->label()])->all()),
            ...$this->extraSettings(),
        ];
    }

    /**
     * @return array<int, Field>
     */
    protected function extraSettings(): array
    {
        return [];
    }

    public function settingsDefaults(): array
    {
        return $this->defaultsFor($this->settings());
    }

    public function settingsValidator(array $settings): Validator
    {
        return $this->validatorFor($this->settings(), $settings);
    }

    public function view(): string
    {
        return "bricks.web.{$this->key()}";
    }

    /**
     * @param  array<int, Field>  $fields
     * @return array<string, mixed>
     */
    protected function defaultsFor(array $fields): array
    {
        return collect($fields)
            ->mapWithKeys(fn (Field $field) => [$field->name => $field->getDefault()])
            ->all();
    }

    /**
     * @param  array<int, Field>  $fields
     * @return array<string, array<int, mixed>>
     */
    protected function rulesFor(array $fields): array
    {
        return collect($fields)
            ->mapWithKeys(fn (Field $field) => [$field->name => $field->getRules()])
            ->all();
    }

    /**
     * Validates against the fields, with their labels in the validation messages.
     *
     * @param  array<int, Field>  $fields
     * @param  array<string, mixed>  $data
     */
    protected function validatorFor(array $fields, array $data): Validator
    {
        return ValidatorFacade::make(
            $data,
            $this->rulesFor($fields),
            attributes: collect($fields)->mapWithKeys(fn (Field $field) => [$field->name => mb_strtolower($field->getLabel())])->all(),
        );
    }
}
