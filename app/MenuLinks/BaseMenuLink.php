<?php

namespace App\MenuLinks;

use App\Fields\FieldSet;
use Illuminate\Validation\Validator;

/**
 * Derives the defaults and validation of a menu link type from its fields.
 */
abstract class BaseMenuLink implements MenuLinkType
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
        return FieldSet::of($this->fields())->defaults();
    }

    public function validator(array $data): Validator
    {
        return FieldSet::of($this->fields())->validator($data);
    }

    public function suggestedLabel(array $data): ?string
    {
        return null;
    }

    public function opensInNewTab(array $data): bool
    {
        return false;
    }
}
