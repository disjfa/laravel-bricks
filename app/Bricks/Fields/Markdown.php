<?php

namespace App\Bricks\Fields;

/**
 * Text that is rendered as Markdown on the website, with HTML escaped.
 */
class Markdown extends Textarea
{
    protected int $maxLength = 20000;

    public function getHelp(): ?string
    {
        return $this->help ?? __('Supports Markdown: **bold**, *italic*, [links](https://example.com) and lists.');
    }

    public function view(): string
    {
        return 'bricks.fields.markdown';
    }
}
