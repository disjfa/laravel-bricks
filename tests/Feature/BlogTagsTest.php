<?php

use App\Enums\ApiAbility;
use App\Enums\BlogTagType;
use App\Models\Blog;
use App\Models\Brick;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Tags\Tag;

describe('admin', function () {
    beforeEach(function () {
        $this->seed(RolesSeeder::class);
        $this->actingAs(User::factory()->admin()->create());
    });

    it('saves categories and tags, and makes the ones that do not exist yet', function () {
        Tag::findOrCreate('News', BlogTagType::Category->value);
        $blog = Blog::factory()->create();

        Livewire::test('pages::admin.blogs.settings', ['blog' => $blog])
            ->assertSeeHtml('data-test="blog-categories"')
            ->set('categories', ['News', ' Tutorials '])
            ->set('tags', ['laravel', 'Livewire', 'LARAVEL', ''])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('categories', ['News', 'Tutorials'])
            ->assertSet('tags', ['laravel', 'Livewire']);

        $blog->load('tags');

        expect($blog->tagsOfType(BlogTagType::Category)->pluck('name')->all())->toBe(['News', 'Tutorials'])
            ->and($blog->tagsOfType(BlogTagType::Tag)->pluck('name')->all())->toBe(['laravel', 'Livewire'])
            ->and(Tag::getWithType(BlogTagType::Category->value))->toHaveCount(2);
    });

    it('offers the existing categories and tags', function () {
        Tag::findOrCreate('Releases', BlogTagType::Category->value);
        Tag::findOrCreate('api', BlogTagType::Tag->value);

        Livewire::test('pages::admin.blogs.settings', ['blog' => Blog::factory()->create()])
            ->assertSeeHtml('<option value="Releases"')
            ->assertSeeHtml('<option value="api"');
    });

    it('removes categories and tags', function () {
        $blog = Blog::factory()->create();
        $blog->attachTag('News', BlogTagType::Category->value);
        $blog->attachTag('laravel', BlogTagType::Tag->value);

        Livewire::test('pages::admin.blogs.settings', ['blog' => $blog])
            ->assertSet('tags', ['laravel'])
            ->set('categories', [])
            ->set('tags', [])
            ->call('save')
            ->assertHasNoErrors();

        expect($blog->refresh()->tags)->toBeEmpty();
    });

    it('rejects names that are too long', function () {
        Livewire::test('pages::admin.blogs.settings', ['blog' => Blog::factory()->create()])
            ->set('categories', [str_repeat('a', 51)])
            ->call('save')
            ->assertHasErrors(['categories.0']);
    });

    it('shows the categories and tags in the blog list and filters it by them', function () {
        $news = Blog::factory()->create(['title' => 'In the news']);
        $news->attachTag('News', BlogTagType::Category->value);
        $news->attachTag('laravel', BlogTagType::Tag->value);
        Blog::factory()->create(['title' => 'Without category']);

        Livewire::test('pages::admin.blogs.index')
            ->assertSee(['News', 'laravel', 'Without category'])
            ->set('category', 'news')
            ->assertSee('In the news')
            ->assertDontSee('Without category')
            ->set('category', '')
            ->set('tag', 'laravel')
            ->assertSee('In the news')
            ->assertDontSee('Without category');
    });
});

describe('website', function () {
    beforeEach(function () {
        $this->news = Blog::factory()->published()->create(['title' => 'In the news', 'slug' => 'in-the-news']);
        $this->news->attachTag('News', BlogTagType::Category->value);
        $this->news->attachTag('laravel', BlogTagType::Tag->value);

        $this->other = Blog::factory()->published()->create(['title' => 'Something else']);
        $this->other->attachTag('Releases', BlogTagType::Category->value);

        $concept = Blog::factory()->create(['title' => 'Concept news']);
        $concept->attachTag('News', BlogTagType::Category->value);
    });

    it('links the categories and tags of a blog', function () {
        $this->get('/blog/in-the-news')
            ->assertOk()
            ->assertSee('href="'.route('blogs.category', 'news').'"', false)
            ->assertSee('href="'.route('blogs.tag', 'laravel').'"', false);
    });

    it('lists the published blogs of a category', function () {
        $this->get(route('blogs.category', 'news'))
            ->assertOk()
            ->assertSee('In the news')
            ->assertDontSee('Something else')
            ->assertDontSee('Concept news');
    });

    it('lists the published blogs with a tag', function () {
        $this->get(route('blogs.tag', 'laravel'))
            ->assertOk()
            ->assertSee('In the news')
            ->assertDontSee('Something else');
    });

    it('returns 404 for an unknown category, or a tag asked for as category', function () {
        $this->get(route('blogs.category', 'nope'))->assertNotFound();
        $this->get(route('blogs.category', 'laravel'))->assertNotFound();
    });

    it('limits the blogs brick to a category', function () {
        $page = Page::factory()->published()->create(['slug' => 'releases']);
        $category = BlogTagType::Category->findBySlug('releases');
        Brick::factory()->for($page, 'brickable')->create([
            'type' => 'blogs',
            'data' => ['title' => '', 'per_page' => 6, 'sort' => 'newest', 'category' => (string) $category->id],
        ]);

        $this->get('/releases')
            ->assertOk()
            ->assertSee('Something else')
            ->assertDontSee('In the news');
    });

    it('filters and shows categories and tags in the api', function () {
        Sanctum::actingAs(User::factory()->create(), [ApiAbility::ContentRead->value]);

        $this->getJson(route('api.v1.blogs.index', ['filter' => ['category' => 'news,releases']]))
            ->assertJsonCount(2, 'data');

        $this->getJson(route('api.v1.blogs.index', ['filter' => ['tag' => 'laravel']]))
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'In the news')
            ->assertJsonPath('data.0.categories.0', ['name' => 'News', 'slug' => 'news', 'url' => route('blogs.category', 'news')])
            ->assertJsonPath('data.0.tags.0.slug', 'laravel');
    });
});
