<?php

use App\Features\Blogs;
use App\Features\Forms;
use App\Features\SiteMenu;
use App\Http\Controllers\BlogsController;
use App\Http\Controllers\ImpersonationsController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\PagesController;
use App\Models\Blog;
use App\Models\Form;
use App\Models\Menu;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

Route::get('/', [PagesController::class, 'home'])->name('home');

Route::get('manifest.webmanifest', ManifestController::class)->name('manifest');

Route::prefix('blog')
    ->name('blogs.')
    ->middleware(EnsureFeaturesAreActive::using(Blogs::class))
    ->group(function () {
        Route::get('category/{slug}', [BlogsController::class, 'category'])->name('category');
        Route::get('tag/{slug}', [BlogsController::class, 'tag'])->name('tag');
        Route::get('{blog:slug}', [BlogsController::class, 'show'])->name('show');
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

        Route::middleware(EnsureFeaturesAreActive::using(Blogs::class))->group(function () {
            Route::livewire('blogs', 'pages::admin.blogs.index')
                ->can('viewAny', Blog::class)
                ->name('blogs.index');

            Route::livewire('blogs/{blog}/edit', 'pages::admin.blogs.edit')
                ->can('update', 'blog')
                ->name('blogs.edit');

            Route::livewire('blogs/{blog}/settings', 'pages::admin.blogs.settings')
                ->can('update', 'blog')
                ->name('blogs.settings');

            Route::livewire('blogs/{blog}/media', 'pages::admin.blogs.media')
                ->can('update', 'blog')
                ->name('blogs.media');
        });

        Route::middleware(EnsureFeaturesAreActive::using(Forms::class))->group(function () {
            Route::livewire('forms', 'pages::admin.forms.index')
                ->can('viewAny', Form::class)
                ->name('forms.index');

            Route::livewire('forms/{form}/edit', 'pages::admin.forms.edit')
                ->can('update', 'form')
                ->name('forms.edit');

            Route::livewire('forms/{form}/settings', 'pages::admin.forms.settings')
                ->can('update', 'form')
                ->name('forms.settings');

            Route::livewire('forms/{form}/submissions', 'pages::admin.forms.submissions')
                ->can('update', 'form')
                ->name('forms.submissions');
        });

        Route::middleware(EnsureFeaturesAreActive::using(SiteMenu::class))->group(function () {
            Route::livewire('menus', 'pages::admin.menus.index')
                ->can('viewAny', Menu::class)
                ->name('menus.index');

            Route::livewire('menus/{menu}/edit', 'pages::admin.menus.edit')
                ->can('update', 'menu')
                ->name('menus.edit');
        });

        Route::livewire('settings', 'pages::admin.settings')
            ->can('manageSettings')
            ->name('settings');

        Route::livewire('api-tokens', 'pages::admin.api-tokens')
            ->can('manageApiTokens')
            ->name('api-tokens');

        Route::livewire('media', 'pages::admin.media.index')
            ->can('viewAny', Media::class)
            ->name('media.index');
    });

Route::delete('impersonation', [ImpersonationsController::class, 'destroy'])
    ->middleware('auth')
    ->name('impersonation.destroy');

require __DIR__.'/settings.php';

Route::fallback([PagesController::class, 'show']);
