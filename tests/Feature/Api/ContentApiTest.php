<?php

use App\Enums\ApiAbility;
use App\Features\Blogs;
use App\Models\Blog;
use App\Models\Brick;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use Laravel\Pennant\Feature;
use Laravel\Sanctum\Sanctum;

function actingAsApiClient(array $abilities = [ApiAbility::ContentRead->value]): User
{
    return Sanctum::actingAs(User::factory()->create(), $abilities);
}

describe('authentication', function () {
    it('requires a token', function () {
        $this->getJson(route('api.v1.pages.index'))->assertUnauthorized();
    });

    it('requires a token that may read content', function () {
        actingAsApiClient([]);

        $this->getJson(route('api.v1.pages.index'))->assertForbidden();
    });

    it('accepts a real token in the authorization header', function () {
        $token = User::factory()->create()->createToken('Test', [ApiAbility::ContentRead->value])->plainTextToken;

        $this->withToken($token)->getJson(route('api.v1.pages.index'))->assertOk();
    });
});

describe('pages', function () {
    beforeEach(fn () => actingAsApiClient());

    it('lists only published pages', function () {
        Page::factory()->published()->create(['title' => 'Visible', 'slug' => 'visible']);
        Page::factory()->create(['title' => 'Concept']);
        Page::factory()->published()->create(['title' => 'Later', 'published_at' => now()->addDay()]);

        $this->getJson(route('api.v1.pages.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Visible')
            ->assertJsonPath('data.0.url', url('/visible'))
            ->assertJsonMissingPath('data.0.bricks');
    });

    it('filters, sorts and includes bricks with the query builder', function () {
        $about = Page::factory()->published()->create(['title' => 'About us']);
        Page::factory()->published()->create(['title' => 'Contact']);
        Page::factory()->published()->create(['title' => 'About the team']);
        Brick::factory()->for($about, 'brickable')->heading('Hello')->create();

        $this->getJson(route('api.v1.pages.index', ['filter' => ['title' => 'about'], 'sort' => 'title', 'include' => 'bricks']))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'About the team')
            ->assertJsonPath('data.1.title', 'About us')
            ->assertJsonPath('data.1.bricks.0.type', 'heading')
            ->assertJsonPath('data.1.bricks.0.data.text', 'Hello')
            ->assertJsonPath('data.1.bricks.0.settings.width', 'normal');
    });

    it('rejects filters and sorts that are not allowed', function () {
        $this->getJson(route('api.v1.pages.index', ['filter' => ['meta_title' => 'x']]))->assertBadRequest();
        $this->getJson(route('api.v1.pages.index', ['sort' => 'password']))->assertBadRequest();
    });

    it('pages the results, at most 100 at a time', function () {
        Page::factory()->count(3)->published()->create();

        $this->getJson(route('api.v1.pages.index', ['per_page' => 2]))
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 2);

        $this->getJson(route('api.v1.pages.index', ['per_page' => 1000]))->assertJsonPath('meta.per_page', 100);
    });

    it('shows a published page with its bricks and seo data', function () {
        $page = Page::factory()->published()->create(['slug' => 'about', 'title' => 'About', 'meta_title' => 'About us']);
        Brick::factory()->for($page, 'brickable')->create(['data' => ['body' => 'Hi']]);
        Brick::factory()->for($page, 'brickable')->create(['type' => 'removed-type', 'data' => [], 'position' => 1]);

        $this->getJson(route('api.v1.pages.show', 'about'))
            ->assertOk()
            ->assertJsonPath('data.seo.title', 'About us')
            ->assertJsonPath('data.seo.noindex', false)
            ->assertJsonCount(1, 'data.bricks')
            ->assertJsonPath('data.bricks.0.data.body', 'Hi');
    });

    it('does not show a page that is not published', function () {
        Page::factory()->create(['slug' => 'draft']);

        $this->getJson(route('api.v1.pages.show', 'draft'))->assertNotFound();
    });
});

describe('blogs', function () {
    beforeEach(fn () => actingAsApiClient());

    it('lists published blogs, newest first', function () {
        Blog::factory()->published()->create(['title' => 'Older', 'published_at' => now()->subDays(2)]);
        Blog::factory()->published()->create(['title' => 'Newer', 'published_at' => now()->subDay(), 'image_url' => '/storage/1/cover.jpg']);
        Blog::factory()->create(['title' => 'Concept']);

        $this->getJson(route('api.v1.blogs.index'))
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Newer')
            ->assertJsonPath('data.0.image', url('/storage/1/cover.jpg'));
    });

    it('is not there while blogs are turned off', function () {
        Feature::deactivate(Blogs::class);
        Blog::factory()->published()->create(['slug' => 'post']);

        $this->getJson(route('api.v1.blogs.index'))->assertNotFound();
        $this->getJson(route('api.v1.blogs.show', 'post'))->assertNotFound();
    });
});

describe('menus', function () {
    beforeEach(fn () => actingAsApiClient());

    it('shows a menu with only the items that link somewhere visible', function () {
        $menu = Menu::factory()->main()->create();
        MenuItem::factory()->for($menu)->forPage(Page::factory()->published()->create(['slug' => 'about']))->create(['label' => 'About', 'position' => 0]);
        MenuItem::factory()->for($menu)->forPage(Page::factory()->create())->create(['label' => 'Concept page', 'position' => 1]);
        MenuItem::factory()->for($menu)->url('https://example.com', newTab: true)->create(['label' => 'Elsewhere', 'position' => 2]);

        $this->getJson(route('api.v1.menus.show', 'main'))
            ->assertOk()
            ->assertJsonPath('data.handle', 'main')
            ->assertJsonCount(2, 'data.items')
            ->assertJsonPath('data.items.0', ['label' => 'About', 'type' => 'page', 'url' => url('/about'), 'new_tab' => false])
            ->assertJsonPath('data.items.1.new_tab', true);
    });

    it('filters menus by handle', function () {
        Menu::factory()->main()->create();
        Menu::factory()->create(['handle' => 'footer']);

        $this->getJson(route('api.v1.menus.index', ['filter' => ['handle' => 'footer']]))
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.handle', 'footer');
    });
});
