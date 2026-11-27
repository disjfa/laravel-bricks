<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    protected const int DefaultPerPage = 15;

    protected const int MaxPerPage = 100;

    /**
     * The page size from ?per_page, between 1 and 100.
     */
    protected function perPage(Request $request): int
    {
        return max(1, min($request->integer('per_page', self::DefaultPerPage), self::MaxPerPage));
    }
}
