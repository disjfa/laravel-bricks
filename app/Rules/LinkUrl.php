<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A link on the website: relative, an anchor, a mail link or an http(s) URL. This keeps `javascript:` URLs
 * out of rendered pages and menus.
 */
class LinkUrl implements ValidationRule
{
    public const string Pattern = '/^(\/|#|https?:\/\/|mailto:)/i';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match(self::Pattern, $value) !== 1) {
            $fail(__('The :attribute must start with /, #, http(s):// or mailto:.'));
        }
    }
}
