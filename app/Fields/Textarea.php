<?php

namespace App\Fields;

class Textarea extends Field
{
    protected int $rows = 3;

    protected int $maxLength = 2000;

    public function rows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function maxLength(int $maxLength): static
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    public function getRows(): int
    {
        return $this->rows;
    }

    protected function typeRules(): array
    {
        return ['string', "max:{$this->maxLength}"];
    }

    public function view(): string
    {
        return 'fields.textarea';
    }
}
