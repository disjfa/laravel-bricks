<?php

namespace App\Enums;

/**
 * What an API token may do, see routes/api.php.
 */
enum ApiAbility: string
{
    case ContentRead = 'content:read';
}
