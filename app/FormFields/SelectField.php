<?php

namespace App\FormFields;

use App\Fields\Text;
use App\Fields\Textarea;
use Illuminate\Validation\Rule;

class SelectField extends BaseFormField
{
    public function key(): string
    {
        return 'select';
    }

    public function label(): string
    {
        return __('Select');
    }

    public function icon(): string
    {
        return 'chevron-up-down';
    }

    public function order(): int
    {
        return 70;
    }

    protected function extraSettings(): array
    {
        return [
            Text::make('placeholder')->label(__('Placeholder'))->half(),
            Textarea::make('options')->label(__('Options'))->help(__('One option per line.'))->required()->rows(4),
        ];
    }

    protected function valueRules(array $settings): array
    {
        return ['string', Rule::in($this->options($settings))];
    }

    /**
     * The options entered one per line.
     *
     * @param  array<string, mixed>  $settings
     * @return array<int, string>
     */
    public function options(array $settings): array
    {
        return collect(preg_split('/\R/', (string) ($settings['options'] ?? '')))
            ->map(fn (string $option) => trim($option))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
