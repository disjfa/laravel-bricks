<?php

namespace App\Fields;

class Select extends Field
{
    /**
     * @var array<string|int, string>
     */
    protected array $options = [];

    /**
     * @param  array<string|int, string>  $options  value => label
     */
    public function options(array $options): static
    {
        $this->options = $options;

        if ($this->default === '') {
            $this->default = array_key_first($options);
        }

        return $this;
    }

    /**
     * @return array<string|int, string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    protected function typeRules(): array
    {
        return ['in:'.implode(',', array_keys($this->options))];
    }

    public function view(): string
    {
        return 'fields.select';
    }
}
