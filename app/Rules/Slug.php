<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A slug or handle: lowercase letters and numbers, with single dashes between them, like "about-us".
 */
class Slug implements ValidationRule
{
    public const string Pattern = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match(self::Pattern, $value) !== 1) {
            $fail(__('The :attribute may only contain lowercase letters, numbers and dashes.'));
        }
    }
}
