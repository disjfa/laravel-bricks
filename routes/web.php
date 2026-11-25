<?php

use App\Http\Controllers\ImpersonationsController;
use App\Http\Controllers\PagesController;
use App\Http\Middleware\EnsureTeamMembership;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

Route::get('/', [PagesController::class, 'home'])->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'can:accessAdmin'])
    ->group(function () {
        Route::livewire('/', 'pages::admin.dashboard')->name('dashboard');

        Route::livewire('users', 'pages::admin.users.index')
            ->can('viewAny', User::class)
            ->name('users.index');

        Route::post('users/{user}/impersonate', [ImpersonationsController::class, 'store'])
            ->name('users.impersonate');

        Route::livewire('pages', 'pages::admin.pages.index')
            ->can('viewAny', Page::class)
            ->name('pages.index');

        Route::livewire('pages/{page}/edit', 'pages::admin.pages.edit')
            ->can('update', 'page')
            ->name('pages.edit');

        Route::livewire('pages/{page}/settings', 'pages::admin.pages.settings')
            ->can('update', 'page')
            ->name('pages.settings');

        Route::livewire('pages/{page}/media', 'pages::admin.pages.media')
            ->can('update', 'page')
            ->name('pages.media');

        Route::livewire('media', 'pages::admin.media.index')
            ->can('viewAny', Media::class)
            ->name('media.index');
    });

Route::delete('impersonation', [ImpersonationsController::class, 'destroy'])
    ->middleware('auth')
    ->name('impersonation.destroy');

require __DIR__.'/settings.php';

Route::fallback([PagesController::class, 'show']);
