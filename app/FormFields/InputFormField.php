<?php

namespace App\FormFields;

use App\Fields\Text;

/**
 * A single line input with a placeholder, rendered with the form-fields.input view.
 */
abstract class InputFormField extends BaseFormField
{
    /**
     * The type attribute of the input element.
     */
    abstract public function inputType(): string;

    protected function extraSettings(): array
    {
        return [
            Text::make('placeholder')->label(__('Placeholder'))->half(),
        ];
    }

    public function view(): string
    {
        return 'form-fields.input';
    }
}
