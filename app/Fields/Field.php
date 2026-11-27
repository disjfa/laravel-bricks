<?php

namespace App\Fields;

use Illuminate\Support\Str;

/**
 * One input an editor fills in, used for the data of bricks and the settings of form fields:
 * its type, label, default value, validation and the admin view that edits it.
 */
abstract class Field
{
    protected string $label;

    protected bool $isRequired = false;

    protected mixed $default = '';

    /**
     * @var array<int, mixed>
     */
    protected array $extraRules = [];

    protected bool $isHalfWidth = false;

    protected ?string $placeholder = null;

    protected ?string $help = null;

    final public function __construct(public string $name)
    {
        $this->label = Str::headline($name);
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * The rules that belong to the field type, like a max length or allowed values.
     *
     * @return array<int, mixed>
     */
    abstract protected function typeRules(): array;

    /**
     * The admin Blade view that edits this field.
     */
    abstract public function view(): string;

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function required(bool $isRequired = true): static
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @param  array<int, mixed>  $rules
     */
    public function rules(array $rules): static
    {
        $this->extraRules = [...$this->extraRules, ...$rules];

        return $this;
    }

    /**
     * Take half the width of the brick editor on larger screens.
     */
    public function half(): static
    {
        $this->isHalfWidth = true;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function help(string $help): static
    {
        $this->help = $help;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * @return array<int, mixed>
     */
    public function getRules(): array
    {
        return [
            $this->isRequired ? 'required' : 'nullable',
            ...$this->typeRules(),
            ...$this->extraRules,
        ];
    }

    public function isHalfWidth(): bool
    {
        return $this->isHalfWidth;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }
}
