<?php

use App\Models\Brick;
use App\Models\Page;
use App\Models\User;
use App\Settings\ThemeSettings;
use Database\Seeders\RolesSeeder;

it('renders a published page and its bricks by slug', function () {
    $page = Page::factory()->published()->create(['slug' => 'about', 'title' => 'About us']);
    Brick::factory()->for($page, 'brickable')->heading('Our story')->create(['position' => 0]);
    Brick::factory()->for($page, 'brickable')->create([
        'type' => 'text',
        'data' => ['body' => 'We **build** things.'],
        'position' => 1,
    ]);

    $this->get('/about')
        ->assertOk()
        ->assertSee('<title>', false)
        ->assertSee('About us')
        ->assertSeeInOrder(['<h2', 'Our story', '<strong>build</strong>'], false);
});

it('returns 404 for an unknown slug', function () {
    $this->get('/does-not-exist')->assertNotFound();
});

it('returns 404 for a draft page to guests', function () {
    Page::factory()->create(['slug' => 'secret']);

    $this->get('/secret')->assertNotFound();
});

it('shows a draft page to admins', function () {
    $this->seed(RolesSeeder::class);
    Page::factory()->create(['slug' => 'secret']);

    $this->actingAs(User::factory()->admin()->create())
        ->get('/secret')
        ->assertOk()
        ->assertSee('This page is not published (concept) and only visible to editors.');
});

it('escapes html in text bricks', function () {
    $page = Page::factory()->published()->create(['slug' => 'xss']);
    Brick::factory()->for($page, 'brickable')->create(['data' => ['body' => '<script>alert(1)</script>']]);

    $this->get('/xss')
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});

it('renders the page with the home slug on the front page', function () {
    $page = Page::factory()->published()->create(['slug' => 'home']);
    Brick::factory()->for($page, 'brickable')->heading('Welcome home')->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Welcome home');
});

it('falls back to the welcome screen without a home page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertViewIs('welcome');
});

it('renders the hero background image', function () {
    $page = Page::factory()->published()->create(['slug' => 'with-hero']);
    Brick::factory()->for($page, 'brickable')->create([
        'type' => 'hero',
        'data' => ['title' => 'Big hero', 'image_url' => 'https://example.com/hero.jpg'],
    ]);

    $this->get('/with-hero')
        ->assertOk()
        ->assertSee('Big hero')
        ->assertSee('src="https://example.com/hero.jpg"', false);
});

it('renders media images inside text bricks', function () {
    $page = Page::factory()->published()->create(['slug' => 'with-image']);
    Brick::factory()->for($page, 'brickable')->create(['data' => ['body' => "Look:\n\n![Sunset](</storage/1/sunset photo.jpg>)"]]);

    $this->get('/with-image')
        ->assertOk()
        ->assertSee('<img src="/storage/1/sunset%20photo.jpg" alt="Sunset" />', false);
});

it('renders bricks in the container for their width', function (?array $settings, string $width, string $containerClass) {
    $page = Page::factory()->published()->create(['slug' => 'widths']);
    Brick::factory()->for($page, 'brickable')->heading('Sized')->create(['settings' => $settings]);

    $this->get('/widths')
        ->assertOk()
        ->assertSee('class="'.$containerClass.'" data-brick-width="'.$width.'"', false);
})->with([
    'without settings' => [null, 'normal', 'mx-auto w-full max-w-3xl px-6'],
    'wide' => [['width' => 'wide'], 'wide', 'mx-auto w-full max-w-6xl px-6'],
    'full' => [['width' => 'full'], 'full', 'w-full px-6'],
]);

it('aligns the text of a text brick', function (?array $settings, string $alignClass) {
    $page = Page::factory()->published()->create(['slug' => 'aligned']);
    Brick::factory()->for($page, 'brickable')->create(['data' => ['body' => 'Aligned text'], 'settings' => $settings]);

    $this->get('/aligned')
        ->assertOk()
        ->assertSee('prose-img:object-cover '.$alignClass.'"', false);
})->with([
    'without settings' => [null, 'text-start'],
    'center' => [['align' => 'center'], 'text-center'],
    'right' => [['align' => 'end'], 'text-end'],
]);

it('aligns a heading brick', function (?array $settings, string $alignClass) {
    $page = Page::factory()->published()->create(['slug' => 'aligned-heading']);
    Brick::factory()->for($page, 'brickable')->heading('Aligned heading')->create(['settings' => $settings]);

    $this->get('/aligned-heading')
        ->assertOk()
        ->assertSee('text-balance '.$alignClass.'">Aligned heading</h2>', false);
})->with([
    'without settings' => [null, 'text-start'],
    'center' => [['align' => 'center'], 'text-center'],
    'right' => [['align' => 'end'], 'text-end'],
]);

it('uses the theme colors from the settings', function () {
    updateSettings(ThemeSettings::class, ['light' => ['accent' => '#1d4ed8', 'menubar_background' => '#fefce8']]);
    Page::factory()->published()->create(['slug' => 'home']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('--color-accent: #1d4ed8;', false)
        ->assertSee('--site-menubar-background: #fefce8;', false)
        ->assertSee('--site-background: #ffffff;', false);
});

it('links editors from the footer to the brick editor of the page', function () {
    $this->seed(RolesSeeder::class);
    $page = Page::factory()->published()->create(['slug' => 'about']);

    $this->actingAs(User::factory()->admin()->create())
        ->get('/about')
        ->assertSee('href="'.route('admin.pages.edit', $page).'"', false)
        ->assertSee('Edit page');
});

it('shows no edit link to visitors who can not edit the page', function () {
    $this->seed(RolesSeeder::class);
    Page::factory()->published()->create(['slug' => 'about']);

    $this->get('/about')->assertDontSee('data-test="edit-content-link"', false);

    $this->actingAs(User::factory()->create())
        ->get('/about')
        ->assertDontSee('data-test="edit-content-link"', false);
});
