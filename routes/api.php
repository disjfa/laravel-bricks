<?php

use App\Enums\ApiAbility;
use App\Features\Blogs;
use App\Features\SiteMenu;
use App\Http\Controllers\Api\V1\BlogsController;
use App\Http\Controllers\Api\V1\MenusController;
use App\Http\Controllers\Api\V1\PagesController;
use Illuminate\Support\Facades\Route;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;

/*
 * Read-only content API, for a token made in the admin (Admin → API tokens). Lists accept the filters,
 * sorts and includes of App\Queries. The OpenAPI document lives at /docs/api.json.
 */
Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['auth:sanctum', 'abilities:'.ApiAbility::ContentRead->value, 'throttle:api'])
    ->group(function () {
        Route::get('pages', [PagesController::class, 'index'])->name('pages.index');
        Route::get('pages/{slug}', [PagesController::class, 'show'])->name('pages.show');

        Route::middleware(EnsureFeaturesAreActive::using(Blogs::class))->group(function () {
            Route::get('blogs', [BlogsController::class, 'index'])->name('blogs.index');
            Route::get('blogs/{slug}', [BlogsController::class, 'show'])->name('blogs.show');
        });

        Route::middleware(EnsureFeaturesAreActive::using(SiteMenu::class))->group(function () {
            Route::get('menus', [MenusController::class, 'index'])->name('menus.index');
            Route::get('menus/{handle}', [MenusController::class, 'show'])->name('menus.show');
        });
    });
