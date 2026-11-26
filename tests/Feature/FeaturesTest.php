<?php

use App\Bricks\BrickRegistry;
use App\Features\Blogs;
use App\Features\Forms;
use App\Features\SiteMenu;
use App\Models\Blog;
use App\Models\Brick;
use App\Models\Form;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

describe('blogs turned off', function () {
    beforeEach(fn () => Feature::deactivate(Blogs::class));

    it('hides blogs on the website', function () {
        Blog::factory()->published()->create(['slug' => 'post']);

        $this->get('/blog/post')->assertNotFound();
    });

    it('hides the blogs admin', function () {
        $this->seed(RolesSeeder::class);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.blogs.index'))
            ->assertNotFound();

        $this->get(route('admin.dashboard'))->assertDontSee(route('admin.blogs.index'));
    });

    it('can no longer add the blogs brick and does not render it', function () {
        expect(app(BrickRegistry::class)->available())->not->toHaveKey('blogs');

        $page = Page::factory()->published()->create(['slug' => 'news']);
        Brick::factory()->for($page, 'brickable')->create(['type' => 'blogs', 'data' => ['title' => 'Latest news', 'per_page' => 6, 'sort' => 'newest']]);

        $this->get('/news')->assertOk()->assertDontSee('Latest news');
    });

    it('hides menu items that link to a blog', function () {
        $item = MenuItem::factory()->forBlog(Blog::factory()->published()->create())->create();

        expect($item->url())->toBeNull();
    });
});

describe('forms turned off', function () {
    beforeEach(fn () => Feature::deactivate(Forms::class));

    it('renders no form and accepts no submissions', function () {
        $form = Form::factory()->contact()->create();

        Livewire::test('site-form', ['formId' => $form->id])
            ->assertDontSee('Email')
            ->call('submit')
            ->assertNotFound();

        expect(app(BrickRegistry::class)->available())->not->toHaveKey('form');
    });

    it('hides the forms admin', function () {
        $this->seed(RolesSeeder::class);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.forms.index'))
            ->assertNotFound();
    });
});

describe('site menu turned off', function () {
    beforeEach(fn () => Feature::deactivate(SiteMenu::class));

    it('shows no menu items on the website', function () {
        $menu = Menu::factory()->main()->create();
        MenuItem::factory()->for($menu)->create(['label' => 'Docs', 'url' => '/docs']);
        Page::factory()->published()->create(['slug' => 'home']);

        $this->get(route('home'))->assertOk()->assertDontSee('Docs');
    });

    it('hides the menus admin', function () {
        $this->seed(RolesSeeder::class);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.menus.index'))
            ->assertNotFound();
    });
});
