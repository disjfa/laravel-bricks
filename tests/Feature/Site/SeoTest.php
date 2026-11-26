<?php

use App\Models\Blog;
use App\Models\Page;
use App\Models\User;
use App\Settings\SeoSettings;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;

it('renders the seo tags of a page', function () {
    Page::factory()->published()->create([
        'slug' => 'about',
        'title' => 'About',
        'meta_title' => 'All about Brick Works',
        'meta_description' => 'Who we are.',
        'meta_image' => '/storage/1/team.jpg',
    ]);

    $this->get('/about')
        ->assertOk()
        ->assertSee('<title>All about Brick Works</title>', false)
        ->assertSee('<meta property="og:title" content="All about Brick Works">', false)
        ->assertSee('<meta name="description" content="Who we are.">', false)
        ->assertSee('<meta property="og:image" content="'.url('/storage/1/team.jpg').'">', false)
        ->assertSee('<link rel="canonical" href="'.url('/about').'">', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertDontSee('name="robots"', false);
});

it('falls back to the title, the excerpt, the blog image and the site defaults', function () {
    updateSettings(SeoSettings::class, ['meta_title' => 'Brick Works']);
    Blog::factory()->published()->create([
        'slug' => 'post',
        'title' => 'First post',
        'excerpt' => 'The intro.',
        'image_url' => '/storage/1/cover.jpg',
        'meta_title' => null,
        'meta_description' => null,
    ]);

    $this->get('/blog/post')
        ->assertSee('<title>First post - Brick Works</title>', false)
        ->assertSee('<meta name="description" content="The intro.">', false)
        ->assertSee('<meta property="og:image" content="'.url('/storage/1/cover.jpg').'">', false)
        ->assertSee('<meta property="og:type" content="article">', false);
});

it('uses the default share image and description from the settings', function () {
    updateSettings(SeoSettings::class, ['meta_image' => '/storage/1/default.jpg']);
    updateSettings(SeoSettings::class, ['meta_description' => 'Default description.']);
    Page::factory()->published()->create(['slug' => 'plain', 'meta_description' => null]);

    $this->get('/plain')
        ->assertSee('<meta name="description" content="Default description.">', false)
        ->assertSee('<meta property="og:image" content="'.url('/storage/1/default.jpg').'">', false);
});

it('asks search engines not to index', function (Closure $arrange, string $url) {
    $arrange();

    $this->get($url)->assertSee('<meta name="robots" content="noindex, nofollow">', false);
})->with([
    'a page with noindex' => [fn () => Page::factory()->published()->create(['slug' => 'hidden', 'noindex' => true]), '/hidden'],
    'every page while the site is hidden' => [function () {
        updateSettings(SeoSettings::class, ['noindex' => true]);
        Page::factory()->published()->create(['slug' => 'any']);
    }, '/any'],
    'the welcome screen while the site is hidden' => [fn () => updateSettings(SeoSettings::class, ['noindex' => true]), '/'],
]);

it('does not let search engines index an unpublished page that an admin previews', function () {
    $this->seed(RolesSeeder::class);
    Page::factory()->create(['slug' => 'draft']);

    $this->actingAs(User::factory()->admin()->create())
        ->get('/draft')
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('does not leak the tags of one request into the next', function () {
    Page::factory()->published()->create(['slug' => 'first', 'meta_title' => 'First title']);
    Page::factory()->published()->create(['slug' => 'second', 'title' => 'Second']);

    $this->get('/first')->assertSee('<title>First title</title>', false);
    $this->get('/second')->assertDontSee('First title');
});

describe('admin', function () {
    beforeEach(function () {
        $this->seed(RolesSeeder::class);
        $this->actingAs(User::factory()->admin()->create());
    });

    it('saves the seo fields of a page', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.settings', ['page' => $page])
            ->set('metaTitle', 'Custom title')
            ->set('metaDescription', 'Custom description')
            ->set('metaImage', '/storage/1/share.jpg')
            ->set('noindex', true)
            ->call('save')
            ->assertHasNoErrors();

        expect($page->refresh()->only(['meta_title', 'meta_description', 'meta_image', 'noindex']))->toBe([
            'meta_title' => 'Custom title',
            'meta_description' => 'Custom description',
            'meta_image' => '/storage/1/share.jpg',
            'noindex' => true,
        ]);
    });

    it('saves the seo fields of a blog and clears empty ones', function () {
        $blog = Blog::factory()->create(['meta_title' => 'Old']);

        Livewire::test('pages::admin.blogs.settings', ['blog' => $blog])
            ->assertSet('metaTitle', 'Old')
            ->set('metaTitle', '')
            ->call('save')
            ->assertHasNoErrors();

        expect($blog->refresh()->meta_title)->toBeNull();
    });

    it('rejects a javascript share image', function () {
        Livewire::test('pages::admin.pages.settings', ['page' => Page::factory()->create()])
            ->set('metaImage', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors(['metaImage']);
    });

    it('saves the seo defaults on the site settings', function () {
        Livewire::test('pages::admin.settings')
            ->set('metaImage', '/storage/1/default.jpg')
            ->set('noindex', true)
            ->call('save')
            ->assertHasNoErrors();

        expect(app(SeoSettings::class)->image())->toBe('/storage/1/default.jpg')
            ->and(app(SeoSettings::class)->noindex)->toBeTrue();
    });
});
