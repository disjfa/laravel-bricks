<?php

use App\Enums\MenuItemType;
use App\Models\Blog;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->actingAs(User::factory()->admin()->create());
});

it('creates a menu and opens the builder', function () {
    Livewire::test('pages::admin.menus.index')
        ->set('name', 'Main menu')
        ->set('handle', 'main')
        ->call('createMenu')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.menus.edit', Menu::firstWhere('handle', 'main')));
});

it('rejects a handle that is taken', function () {
    Menu::factory()->main()->create();

    Livewire::test('pages::admin.menus.index')
        ->set('name', 'Another')
        ->set('handle', 'main')
        ->call('createMenu')
        ->assertHasErrors(['handle' => 'unique']);
});

it('saves page, blog and custom link items in order', function () {
    $menu = Menu::factory()->create();
    $page = Page::factory()->create(['title' => 'About us']);
    $blog = Blog::factory()->create();

    Livewire::test('pages::admin.menus.edit', ['menu' => $menu])
        ->call('addItem', 'page')
        ->set('items.0.page_id', $page->id)
        ->assertSet('items.0.label', 'About us')
        ->call('addItem', 'blog')
        ->set('items.1.label', 'Our blog')
        ->set('items.1.blog_id', $blog->id)
        ->call('addItem', 'url')
        ->set('items.2.label', 'Docs')
        ->set('items.2.url', 'https://example.com/docs')
        ->call('save')
        ->assertHasNoErrors();

    expect($menu->refresh()->items)->sequence(
        fn ($item) => $item->type->toBe(MenuItemType::Page)->page_id->toBe($page->id)->label->toBe('About us'),
        fn ($item) => $item->type->toBe(MenuItemType::Blog)->blog_id->toBe($blog->id)->label->toBe('Our blog'),
        fn ($item) => $item->type->toBe(MenuItemType::Url)->url->toBe('https://example.com/docs'),
    );
});

it('requires a target for every item', function () {
    Livewire::test('pages::admin.menus.edit', ['menu' => Menu::factory()->create()])
        ->call('addItem', 'page')
        ->call('addItem', 'url')
        ->set('items.0.label', 'A page')
        ->set('items.1.label', 'A link')
        ->call('save')
        ->assertHasErrors([
            'items.0.page_id' => 'Pick a page.',
            'items.1.url' => 'Enter a link.',
        ]);
});

it('rejects javascript links', function () {
    Livewire::test('pages::admin.menus.edit', ['menu' => Menu::factory()->create()])
        ->call('addItem', 'url')
        ->set('items.0.label', 'Click me')
        ->set('items.0.url', 'javascript:alert(1)')
        ->call('save')
        ->assertHasErrors(['items.0.url']);
});

it('removes items', function () {
    $item = MenuItem::factory()->create();

    $component = Livewire::test('pages::admin.menus.edit', ['menu' => $item->menu]);

    $component
        ->call('removeItem', $component->get('items.0.key'))
        ->call('save');

    $this->assertModelMissing($item);
});

it('renders a moved item fresh, so its inputs bind to its new position', function () {
    $menu = Menu::factory()->create();
    MenuItem::factory()->for($menu)->create(['label' => 'First', 'position' => 0]);
    MenuItem::factory()->for($menu)->create(['label' => 'Second', 'position' => 1]);

    $component = Livewire::test('pages::admin.menus.edit', ['menu' => $menu]);
    $secondKey = $component->get('items.1.key');

    $component
        ->assertSeeHtml('wire:key="item-'.$secondKey.'-1"')
        ->call('sortItem', $secondKey, 0)
        ->assertSeeHtml('wire:key="item-'.$secondKey.'-0"')
        ->assertSet('items.0.label', 'Second');
});
