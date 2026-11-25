<?php

use App\Models\Brick;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;

it('renders a published page and its bricks by slug', function () {
    $page = Page::factory()->published()->create(['slug' => 'about', 'title' => 'About us']);
    Brick::factory()->for($page)->heading('Our story')->create(['position' => 0]);
    Brick::factory()->for($page)->create([
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
        ->assertSee(__('This page is a draft and only visible to editors.'));
});

it('escapes html in text bricks', function () {
    $page = Page::factory()->published()->create(['slug' => 'xss']);
    Brick::factory()->for($page)->create(['data' => ['body' => '<script>alert(1)</script>']]);

    $this->get('/xss')
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false);
});

it('renders the page with the home slug on the front page', function () {
    $page = Page::factory()->published()->create(['slug' => 'home']);
    Brick::factory()->for($page)->heading('Welcome home')->create();

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
    Brick::factory()->for($page)->create([
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
    Brick::factory()->for($page)->create(['data' => ['body' => "Look:\n\n![Sunset](</storage/1/sunset photo.jpg>)"]]);

    $this->get('/with-image')
        ->assertOk()
        ->assertSee('<img src="/storage/1/sunset%20photo.jpg" alt="Sunset" />', false);
});
