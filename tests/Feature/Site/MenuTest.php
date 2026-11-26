<?php

use App\Models\Blog;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;

beforeEach(function () {
    $this->menu = Menu::factory()->main()->create();
    Page::factory()->published()->create(['slug' => 'home']);
});

it('shows the items of the main menu in the header', function () {
    $about = Page::factory()->published()->create(['slug' => 'about']);
    $blog = Blog::factory()->published()->create(['slug' => 'news']);
    MenuItem::factory()->for($this->menu)->forPage($about)->create(['label' => 'About us', 'position' => 0]);
    MenuItem::factory()->for($this->menu)->forBlog($blog)->create(['label' => 'Latest news', 'position' => 1]);
    MenuItem::factory()->for($this->menu)->create(['label' => 'Docs', 'url' => 'https://example.com/docs', 'position' => 2]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['About us', 'Latest news', 'Docs'])
        ->assertSee(url('/about'), false)
        ->assertSee(url('/blog/news'), false)
        ->assertSee('https://example.com/docs', false);
});

it('repeats the items in a sidebar for mobile', function () {
    MenuItem::factory()->for($this->menu)->create(['label' => 'Docs', 'url' => '/docs']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-test="site-menu-button"', false)
        ->assertSeeInOrder(['data-test="site-menu-sidebar"', 'Docs'], false);
});

it('hides items that link to something that is not published', function () {
    MenuItem::factory()->for($this->menu)->forPage(Page::factory()->create())->create(['label' => 'Secret page']);
    MenuItem::factory()->for($this->menu)->forBlog(Blog::factory()->closed()->create())->create(['label' => 'Closed blog']);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Secret page')
        ->assertDontSee('Closed blog');
});

it('shows the footer menu', function () {
    $footer = Menu::factory()->create(['handle' => Menu::FooterHandle]);
    MenuItem::factory()->for($footer)->create(['label' => 'Privacy', 'url' => '/privacy']);

    $this->get(route('home'))->assertSee('Privacy');
});

it('has no login, register or account links', function () {
    $this->seed(RolesSeeder::class);

    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee(route('login'), false)
        ->assertDontSee(route('register'), false)
        ->assertDontSee(route('profile.edit'), false)
        ->assertDontSee(route('admin.dashboard'), false);
});

it('links to the admin for admins', function () {
    $this->seed(RolesSeeder::class);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('home'))
        ->assertSee(route('admin.dashboard'), false);
});
