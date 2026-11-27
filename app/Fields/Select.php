<?php

namespace App\Fields;

class Select extends Field
{
    /**
     * @var array<string|int, string>
     */
    protected array $options = [];

    protected bool $isSegmented = false;

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

    /**
     * Show the options side by side as a segmented control instead of a dropdown. For a few short options.
     */
    public function segmented(): static
    {
        $this->isSegmented = true;

        return $this;
    }

    public function isSegmented(): bool
    {
        return $this->isSegmented;
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
