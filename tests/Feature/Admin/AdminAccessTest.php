<?php

use App\Models\Blog;
use App\Models\Form;
use App\Models\Menu;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->seed(RolesSeeder::class);
});

dataset('admin routes', [
    'dashboard' => fn () => route('admin.dashboard'),
    'users' => fn () => route('admin.users.index'),
    'pages' => fn () => route('admin.pages.index'),
    'page editor' => fn () => route('admin.pages.edit', Page::factory()->create()),
    'page settings' => fn () => route('admin.pages.settings', Page::factory()->create()),
    'page media' => fn () => route('admin.pages.media', Page::factory()->create()),
    'media library' => fn () => route('admin.media.index'),
    'blogs' => fn () => route('admin.blogs.index'),
    'blog editor' => fn () => route('admin.blogs.edit', Blog::factory()->create()),
    'blog settings' => fn () => route('admin.blogs.settings', Blog::factory()->create()),
    'blog media' => fn () => route('admin.blogs.media', Blog::factory()->create()),
    'forms' => fn () => route('admin.forms.index'),
    'form builder' => fn () => route('admin.forms.edit', Form::factory()->contact()->create()),
    'form settings' => fn () => route('admin.forms.settings', Form::factory()->create()),
    'form submissions' => fn () => route('admin.forms.submissions', Form::factory()->create()),
    'menus' => fn () => route('admin.menus.index'),
    'settings' => fn () => route('admin.settings'),
    'menu builder' => fn () => route('admin.menus.edit', Menu::factory()->create()),
]);

it('redirects guests to the login page', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with('admin routes');

it('forbids users without the admin role', function (string $url) {
    $user = User::factory()->create();

    $this->actingAs($user)->get($url)->assertForbidden();
})->with('admin routes');

it('renders for admins', function (string $url) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get($url)->assertOk();
})->with('admin routes');

it('shows the admin menu items to admins', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSee(route('admin.pages.index'))
        ->assertSee(route('admin.users.index'));
});
