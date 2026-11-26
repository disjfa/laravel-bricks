<?php

use App\Models\Blog;
use App\Models\Form;
use App\Models\Menu;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    $this->actingAs(User::factory()->admin()->create());
});

it('shows the shared header with a save button on accesskey S', function (Closure $url) {
    $this->get($url())
        ->assertOk()
        ->assertSee('data-test="screen-header"', false)
        ->assertSee('accesskey="s"', false);
})->with([
    'page editor' => fn () => route('admin.pages.edit', Page::factory()->create()),
    'page settings' => fn () => route('admin.pages.settings', Page::factory()->create()),
    'blog editor' => fn () => route('admin.blogs.edit', Blog::factory()->create()),
    'blog settings' => fn () => route('admin.blogs.settings', Blog::factory()->create()),
    'form builder' => fn () => route('admin.forms.edit', Form::factory()->create()),
    'form settings' => fn () => route('admin.forms.settings', Form::factory()->create()),
    'menu builder' => fn () => route('admin.menus.edit', Menu::factory()->create()),
    'site settings' => fn () => route('admin.settings'),
]);

it('shows the shared header without a save button where there is nothing to save', function (Closure $url) {
    $this->get($url())
        ->assertOk()
        ->assertSee('data-test="screen-header"', false)
        ->assertDontSee('accesskey="s"', false);
})->with([
    'page media' => fn () => route('admin.pages.media', Page::factory()->create()),
    'blog media' => fn () => route('admin.blogs.media', Blog::factory()->create()),
    'form submissions' => fn () => route('admin.forms.submissions', Form::factory()->create()),
]);
