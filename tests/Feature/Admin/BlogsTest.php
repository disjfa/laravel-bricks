<?php

use App\Enums\PublishStatus;
use App\Models\Blog;
use App\Models\Brick;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->actingAs(User::factory()->admin()->create());
});

describe('index', function () {
    it('creates a blog with a slug from its title and opens the brick editor', function () {
        Livewire::test('pages::admin.blogs.index')
            ->set('title', 'Hello World')
            ->call('createBlog')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.blogs.edit', Blog::firstWhere('slug', 'hello-world')));
    });

    it('deletes a blog with its bricks', function () {
        $blog = Blog::factory()->create();
        $brick = Brick::factory()->for($blog, 'brickable')->create();

        Livewire::test('pages::admin.blogs.index')
            ->call('deleteBlog', $blog->id);

        $this->assertModelMissing($blog);
        $this->assertModelMissing($brick);
    });
});

describe('editor', function () {
    it('saves bricks on the blog', function () {
        $blog = Blog::factory()->create();

        Livewire::test('pages::admin.blogs.edit', ['blog' => $blog])
            ->assertSeeLivewire('brick-preview')
            ->call('addBrick', 'text')
            ->set('bricks.0.data.body', 'Blog **body**')
            ->call('save')
            ->assertHasNoErrors();

        expect($blog->bricks()->sole())
            ->type->toBe('text')
            ->data->toBe(['body' => 'Blog **body**']);
    });

    it('does not touch the bricks of a page with the same id', function () {
        $blog = Blog::factory()->create();
        $pageBrick = Brick::factory()->create(['brickable_id' => $blog->id]);

        Livewire::test('pages::admin.blogs.edit', ['blog' => $blog])
            ->assertSet('bricks', [])
            ->call('save');

        $this->assertModelExists($pageBrick);
    });
});

describe('settings', function () {
    it('updates the blog settings', function () {
        $blog = Blog::factory()->create();

        Livewire::test('pages::admin.blogs.settings', ['blog' => $blog])
            ->set('title', 'New title')
            ->set('slug', 'new-slug')
            ->set('excerpt', 'Short intro')
            ->set('imageUrl', '/storage/1/cover.jpg')
            ->set('publishing.status', PublishStatus::Active->value)
            ->set('publishing.seqnr', 5)
            ->call('save')
            ->assertHasNoErrors();

        expect($blog->refresh())
            ->title->toBe('New title')
            ->slug->toBe('new-slug')
            ->excerpt->toBe('Short intro')
            ->image_url->toBe('/storage/1/cover.jpg')
            ->seqnr->toBe(5)
            ->isPublished()->toBeTrue();
    });

    it('rejects a javascript image url', function () {
        Livewire::test('pages::admin.blogs.settings', ['blog' => Blog::factory()->create()])
            ->set('imageUrl', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors(['imageUrl']);
    });

    it('rejects a slug that is already taken', function () {
        Blog::factory()->create(['slug' => 'taken']);

        Livewire::test('pages::admin.blogs.settings', ['blog' => Blog::factory()->create()])
            ->set('slug', 'taken')
            ->call('save')
            ->assertHasErrors(['slug' => 'unique']);
    });
});

describe('media', function () {
    it('attaches uploaded files to the blog and offers them in the image picker', function () {
        Storage::fake('public');
        $blog = Blog::factory()->create();

        Livewire::test('pages::admin.blogs.media', ['blog' => $blog])
            ->set('uploads', [UploadedFile::fake()->image('cover.jpg')])
            ->assertHasNoErrors()
            ->assertSee('cover');

        expect($blog->getMedia(Blog::MediaCollection))->toHaveCount(1);

        Livewire::test('pages::admin.blogs.settings', ['blog' => $blog])
            ->assertSee('cover');
    });
});
