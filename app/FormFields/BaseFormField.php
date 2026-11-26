<?php

namespace App\FormFields;

use App\Fields\Field;
use App\Fields\Text;
use App\Fields\Toggle;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

/**
 * Every field has a label, an optional help text and can be required. Types add their own settings
 * with extraSettings() and the rules for the submitted value with valueRules().
 */
abstract class BaseFormField implements FormFieldType
{
    public function order(): int
    {
        return 100;
    }

    public function settings(): array
    {
        return [
            Text::make('label')->label(__('Label'))->required()->half(),
            ...$this->extraSettings(),
            Text::make('help')->label(__('Help text')),
            Toggle::make('required')->label(__('Required')),
        ];
    }

    /**
     * @return array<int, Field>
     */
    protected function extraSettings(): array
    {
        return [];
    }

    public function defaults(): array
    {
        return collect($this->settings())
            ->mapWithKeys(fn (Field $field) => [$field->name => $field->getDefault()])
            ->merge(['label' => $this->label()])
            ->all();
    }

    public function validator(array $settings): Validator
    {
        $fields = collect($this->settings());

        return ValidatorFacade::make(
            $settings,
            $fields->mapWithKeys(fn (Field $field) => [$field->name => $field->getRules()])->all(),
            attributes: $fields->mapWithKeys(fn (Field $field) => [$field->name => mb_strtolower($field->getLabel())])->all(),
        );
    }

    public function rules(array $settings): array
    {
        return [
            ($settings['required'] ?? false) ? 'required' : 'nullable',
            ...$this->valueRules($settings),
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<int, mixed>
     */
    abstract protected function valueRules(array $settings): array;

    public function emptyValue(): mixed
    {
        return '';
    }

    public function view(): string
    {
        return "form-fields.{$this->key()}";
    }
}
