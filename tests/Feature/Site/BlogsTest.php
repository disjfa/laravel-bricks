<?php

use App\Enums\PublishStatus;
use App\Models\Blog;
use App\Models\Brick;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;

it('renders a published blog and its bricks at /blog/{slug}', function () {
    $blog = Blog::factory()->published()->create(['slug' => 'first-post', 'title' => 'First post', 'excerpt' => 'The intro']);
    Brick::factory()->for($blog, 'brickable')->create(['data' => ['body' => 'We **write** things.']]);

    $this->get('/blog/first-post')
        ->assertOk()
        ->assertSeeInOrder(['First post', 'The intro', '<strong>write</strong>'], false);
});

it('hides blogs that are not published from guests', function (array $attributes) {
    Blog::factory()->create(['slug' => 'hidden', ...$attributes]);

    $this->get('/blog/hidden')->assertNotFound();
})->with([
    'concept' => [['status' => PublishStatus::Concept]],
    'closed' => [['status' => PublishStatus::Closed]],
    'scheduled' => [fn () => ['status' => PublishStatus::Active, 'published_at' => now()->addDay()]],
    'expired' => [fn () => ['status' => PublishStatus::Active, 'published_until' => now()->subDay()]],
]);

it('shows a concept blog to admins', function () {
    $this->seed(RolesSeeder::class);
    Blog::factory()->create(['slug' => 'draft']);

    $this->actingAs(User::factory()->admin()->create())
        ->get('/blog/draft')
        ->assertOk()
        ->assertSee('This blog is not published (concept) and only visible to editors.');
});

it('returns 404 for an unknown blog', function () {
    $this->get('/blog/nope')->assertNotFound();
});

describe('blogs brick', function () {
    it('lists the published blogs, newest first', function () {
        Blog::factory()->published()->create(['title' => 'Older', 'published_at' => now()->subDays(2)]);
        Blog::factory()->published()->create(['title' => 'Newer', 'published_at' => now()->subDay()]);
        Blog::factory()->create(['title' => 'Concept blog']);

        $page = Page::factory()->published()->create(['slug' => 'news']);
        Brick::factory()->for($page, 'brickable')->create(['type' => 'blogs', 'data' => ['title' => 'Latest news', 'per_page' => 6, 'sort' => 'newest']]);

        $this->get('/news')
            ->assertOk()
            ->assertSeeInOrder(['Latest news', 'Newer', 'Older'])
            ->assertDontSee('Concept blog');
    });

    it('paginates with livewire', function () {
        foreach (range(1, 4) as $day) {
            Blog::factory()->published()->create(['title' => "Blog {$day}", 'published_at' => now()->subDays($day)]);
        }

        Livewire::test('blog-archive', ['perPage' => 3])
            ->assertSee(['Blog 1', 'Blog 3'])
            ->assertDontSee('Blog 4')
            ->call('nextPage', 'blogs')
            ->assertSee('Blog 4')
            ->assertDontSee('Blog 1');
    });

    it('orders by sequence number', function () {
        Blog::factory()->published()->create(['title' => 'Second', 'seqnr' => 2]);
        Blog::factory()->published()->create(['title' => 'First', 'seqnr' => 1]);

        Livewire::test('blog-archive', ['sort' => 'sequence'])
            ->assertSeeInOrder(['First', 'Second']);
    });
});

it('links editors from the footer to the brick editor of the blog', function () {
    $this->seed(RolesSeeder::class);
    $blog = Blog::factory()->published()->create(['slug' => 'post']);

    $this->actingAs(User::factory()->admin()->create())
        ->get('/blog/post')
        ->assertSee('href="'.route('admin.blogs.edit', $blog).'"', false)
        ->assertSee('Edit blog');
});
