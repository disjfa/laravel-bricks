<?php

use App\Enums\PublishStatus;
use App\Models\Brick;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob;

beforeEach(function () {
    $this->seed(RolesSeeder::class);

    $this->actingAs(User::factory()->admin()->create());
});

describe('index', function () {
    it('creates a page with a slug from its title and opens the editor', function () {
        Livewire::test('pages::admin.pages.index')
            ->set('title', 'Contact Us')
            ->call('createPage')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.pages.edit', Page::firstWhere('slug', 'contact-us')));
    });

    it('requires a title', function () {
        Livewire::test('pages::admin.pages.index')
            ->set('title', '')
            ->call('createPage')
            ->assertHasErrors(['title' => 'required']);

        expect(Page::count())->toBe(0);
    });

    it('deletes a page with its bricks', function () {
        $brick = Brick::factory()->create();
        $page = $brick->brickable;

        Livewire::test('pages::admin.pages.index')
            ->call('deletePage', $page->id);

        $this->assertModelMissing($page);
        $this->assertModelMissing($brick);
    });
});

describe('editor', function () {
    it('saves added bricks in order', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->call('addBrick', 'text')
            ->call('addBrick', 'heading', 0)
            ->set('bricks.0.data.text', 'Welcome')
            ->set('bricks.0.data.level', 3)
            ->set('bricks.1.data.body', 'Hello **world**')
            ->call('save')
            ->assertHasNoErrors();

        expect($page->refresh()->bricks)->sequence(
            fn ($brick) => $brick->type->toBe('heading')->data->toBe(['text' => 'Welcome', 'level' => 3]),
            fn ($brick) => $brick->type->toBe('text')->data->toBe(['body' => 'Hello **world**']),
        );
    });

    it('reorders and removes existing bricks', function () {
        $page = Page::factory()->create();
        $first = Brick::factory()->for($page, 'brickable')->heading('First')->create(['position' => 0]);
        $second = Brick::factory()->for($page, 'brickable')->heading('Second')->create(['position' => 1]);
        $third = Brick::factory()->for($page, 'brickable')->heading('Third')->create(['position' => 2]);

        $component = Livewire::test('pages::admin.pages.edit', ['page' => $page]);
        $keys = array_column($component->get('bricks'), 'key');

        $component
            ->call('sortBrick', $keys[2], 0)
            ->call('removeBrick', $keys[1])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertModelMissing($second);
        expect($page->bricks()->pluck('id')->all())->toBe([$third->id, $first->id]);
    });

    it('moves a brick down and tells the editor to scroll along with it', function () {
        $page = Page::factory()->create();
        Brick::factory()->for($page, 'brickable')->heading('First')->create(['position' => 0]);
        Brick::factory()->for($page, 'brickable')->heading('Second')->create(['position' => 1]);

        $component = Livewire::test('pages::admin.pages.edit', ['page' => $page]);
        $firstKey = $component->get('bricks.0.key');

        $component
            ->call('moveBrick', $firstKey, 1)
            ->assertSet('bricks.1.key', $firstKey)
            ->assertDispatched('brick-moved', key: $firstKey);
    });

    it('rejects a brick that is missing required content', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->call('addBrick', 'heading')
            ->call('save')
            ->assertHasErrors(['bricks.0.data.text' => 'The heading field is required.']);

        expect($page->bricks()->count())->toBe(0);
    });

    it('saves the brick settings, starting at the normal width and left aligned', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->call('addBrick', 'heading')
            ->assertSet('bricks.0.settings', ['width' => 'normal', 'align' => 'start'])
            ->assertSeeHtml('data-test="brick-settings-button"')
            ->set('bricks.0.data.text', 'Welcome')
            ->set('bricks.0.settings.width', 'full')
            ->set('bricks.0.settings.align', 'center')
            ->call('save')
            ->assertHasNoErrors();

        expect($page->bricks()->sole()->settings)->toBe(['width' => 'full', 'align' => 'center']);
    });

    it('rejects an unknown width', function () {
        Livewire::test('pages::admin.pages.edit', ['page' => Page::factory()->create()])
            ->call('addBrick', 'divider')
            ->set('bricks.0.settings.width', 'huge')
            ->call('save')
            ->assertHasErrors(['bricks.0.settings.width']);
    });

    it('gives existing bricks without settings the default settings', function () {
        $page = Page::factory()->create();
        Brick::factory()->for($page, 'brickable')->create(['settings' => null]);

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->assertSet('bricks.0.settings', ['width' => 'normal', 'align' => 'start']);
    });

    it('saves the alignment of a text brick, starting at the left', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->call('addBrick', 'text')
            ->assertSet('bricks.0.settings.align', 'start')
            ->set('bricks.0.data.body', 'Hello')
            ->set('bricks.0.settings.align', 'center')
            ->call('save')
            ->assertHasNoErrors();

        expect($page->bricks()->sole()->settings)->toBe(['width' => 'normal', 'align' => 'center']);
    });

    it('shows the width and alignment settings as segmented controls', function () {
        Livewire::test('pages::admin.pages.edit', ['page' => Page::factory()->create()])
            ->call('addBrick', 'text')
            ->assertSeeHtml('wire:model.live="bricks.0.settings.width"')
            ->assertSeeHtml('wire:model.live="bricks.0.settings.align"')
            ->assertSeeHtml('data-flux-radio-group-segmented');
    });

    it('rejects an unknown alignment', function () {
        Livewire::test('pages::admin.pages.edit', ['page' => Page::factory()->create()])
            ->call('addBrick', 'text')
            ->set('bricks.0.data.body', 'Hello')
            ->set('bricks.0.settings.align', 'diagonal')
            ->call('save')
            ->assertHasErrors(['bricks.0.settings.align']);
    });

    it('rejects javascript links in bricks', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->call('addBrick', 'button')
            ->set('bricks.0.data.label', 'Click me')
            ->set('bricks.0.data.url', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors(['bricks.0.data.url']);
    });
});

describe('live preview', function () {
    it('edits text bricks with the markdown editor', function () {
        $page = Page::factory()->create();
        Brick::factory()->for($page, 'brickable')->create(['data' => ['body' => 'Some **bold** text']]);

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->assertSeeHtml('x-data="markdownEditor"')
            ->assertSeeHtml('data-path="bricks.0.data.body"')
            ->assertSee('Some **bold** text');
    });

    it('offers the page images in the markdown image picker', function () {
        Storage::fake('public');
        $page = Page::factory()->create();
        $page->addMedia(UploadedFile::fake()->image('inline-photo.jpg'))->toMediaCollection(Page::MediaCollection);
        Brick::factory()->for($page, 'brickable')->create();

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->assertSeeHtml('data-test="markdown-image-picker"')
            ->assertSee('inline-photo');
    });

    it('dispatches the unsaved bricks when they change', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->call('addBrick', 'text')
            ->set('bricks.0.data.body', 'Hello')
            ->assertDispatched('bricks-updated', fn (string $event, array $params) => $params['bricks'][0]['data']['body'] === 'Hello');

        expect($page->bricks()->count())->toBe(0);
    });

    it('always shows the preview next to the editor', function () {
        Livewire::test('pages::admin.pages.edit', ['page' => Page::factory()->create()])
            ->assertSeeLivewire('brick-preview');
    });

    it('renders updated bricks with the website views', function () {
        Livewire::test('brick-preview')
            ->dispatch('bricks-updated', bricks: [
                ['key' => 'a', 'type' => 'text', 'data' => ['body' => 'Hello **live** world']],
            ])
            ->assertSeeHtml('<strong>live</strong>');
    });

    it('renders bricks in the preview at their width', function () {
        Livewire::test('brick-preview')
            ->dispatch('bricks-updated', bricks: [
                ['key' => 'a', 'type' => 'divider', 'data' => [], 'settings' => ['width' => 'wide']],
            ])
            ->assertSeeHtml('data-brick-width="wide"');
    });

    it('renders an incomplete or unsafe brick as a placeholder', function () {
        Livewire::test('brick-preview')
            ->dispatch('bricks-updated', bricks: [
                ['key' => 'a', 'type' => 'button', 'data' => ['label' => 'Click me', 'url' => 'javascript:alert(1)', 'variant' => 'primary']],
            ])
            ->assertSee(__(':type: fill in the required fields to preview this brick.', ['type' => 'Button']))
            ->assertDontSeeHtml('javascript:alert(1)');
    });
});

describe('settings', function () {
    it('updates the title, slug, meta description and publish state', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.settings', ['page' => $page])
            ->set('title', 'New title')
            ->set('slug', 'new-slug')
            ->set('metaDescription', 'About this page')
            ->set('publishing.status', PublishStatus::Active->value)
            ->call('save')
            ->assertHasNoErrors();

        expect($page->refresh())
            ->title->toBe('New title')
            ->slug->toBe('new-slug')
            ->meta_description->toBe('About this page')
            ->isPublished()->toBeTrue();
    });

    it('links the public url in the header', function () {
        $page = Page::factory()->create(['slug' => 'about']);

        Livewire::test('pages::admin.pages.settings', ['page' => $page])
            ->assertSeeHtml('href="'.url('/about').'" target="_blank"')
            ->assertDontSee('The page is available at');
    });

    it('rejects a slug that is already taken', function () {
        Page::factory()->create(['slug' => 'taken']);
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.settings', ['page' => $page])
            ->set('slug', 'taken')
            ->call('save')
            ->assertHasErrors(['slug' => 'unique']);
    });

    it('rejects a slug with invalid characters', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.settings', ['page' => $page])
            ->set('slug', 'Not A/Slug')
            ->call('save')
            ->assertHasErrors(['slug' => 'The slug may only contain lowercase letters, numbers and dashes.']);
    });

    it('closes a page', function () {
        $page = Page::factory()->published()->create();

        Livewire::test('pages::admin.pages.settings', ['page' => $page])
            ->set('publishing.status', PublishStatus::Closed->value)
            ->call('save')
            ->assertHasNoErrors();

        expect($page->refresh())
            ->status->toBe(PublishStatus::Closed)
            ->isPublished()->toBeFalse();
    });

    it('saves the publish window and sequence number', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.settings', ['page' => $page])
            ->set('publishing.status', PublishStatus::Active->value)
            ->set('publishing.publishedAt', '2030-01-01T09:00')
            ->set('publishing.publishedUntil', '2030-02-01T09:00')
            ->set('publishing.seqnr', 7)
            ->call('save')
            ->assertHasNoErrors();

        expect($page->refresh())
            ->published_at->format('Y-m-d H:i')->toBe('2030-01-01 09:00')
            ->published_until->format('Y-m-d H:i')->toBe('2030-02-01 09:00')
            ->seqnr->toBe(7)
            ->isScheduled()->toBeTrue();
    });

    it('fills and saves the dates in the format of the date picker', function () {
        $page = Page::factory()->create(['published_at' => '2030-01-01 09:00:00']);

        Livewire::test('pages::admin.pages.settings', ['page' => $page])
            ->assertSet('publishing.publishedAt', '2030-01-01 09:00')
            ->set('publishing.publishedUntil', '2030-03-15 17:30')
            ->call('save')
            ->assertHasNoErrors();

        expect($page->refresh()->published_until->format('Y-m-d H:i'))->toBe('2030-03-15 17:30');
    });

    it('rejects a publish until date before the publish date', function () {
        Livewire::test('pages::admin.pages.settings', ['page' => Page::factory()->create()])
            ->set('publishing.publishedAt', '2030-02-01T09:00')
            ->set('publishing.publishedUntil', '2030-01-01T09:00')
            ->call('save')
            ->assertHasErrors(['publishing.publishedUntil']);
    });
});

describe('media', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    it('attaches uploaded files to the page right away', function () {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.media', ['page' => $page])
            ->set('uploads', [
                UploadedFile::fake()->image('hero.jpg', 1600, 900),
                UploadedFile::fake()->create('brochure.pdf', 200, 'application/pdf'),
            ])
            ->assertHasNoErrors()
            ->assertSet('uploads', [])
            ->assertSee('hero')
            ->assertSee('brochure');

        $media = $page->getMedia(Page::MediaCollection);

        expect($media)->toHaveCount(2)
            ->and($media->firstWhere('name', 'hero')->hasGeneratedConversion('thumb'))->toBeTrue();
        Storage::disk('public')->assertExists($media->firstWhere('name', 'hero')->getPathRelativeToRoot());
    });

    it('queues the thumbnail when a real queue is set up and shows the original until it is made', function () {
        Queue::fake();
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.media', ['page' => $page])
            ->set('uploads', [UploadedFile::fake()->image('hero.jpg', 1600, 900)])
            ->assertHasNoErrors();

        $media = $page->getFirstMedia(Page::MediaCollection);

        Queue::assertPushed(PerformConversionsJob::class);
        expect($media->hasGeneratedConversion('thumb'))->toBeFalse();

        Livewire::test('pages::admin.pages.media', ['page' => $page])
            ->assertSeeHtml('src="'.$media->getUrl().'"');
    });

    it('rejects file types that could run scripts', function (string $fileName) {
        $page = Page::factory()->create();

        Livewire::test('pages::admin.pages.media', ['page' => $page])
            ->set('uploads', [UploadedFile::fake()->create($fileName, 10)])
            ->assertHasErrors(['uploads.0' => 'This file type is not allowed.'])
            ->assertSet('uploads', []);

        expect($page->getMedia(Page::MediaCollection))->toBeEmpty();
    })->with(['drawing.svg', 'page.html', 'shell.php']);

    it('deletes a file from the page', function () {
        $page = Page::factory()->create();
        $media = $page->addMedia(UploadedFile::fake()->image('old.jpg'))->toMediaCollection(Page::MediaCollection);

        Livewire::test('pages::admin.pages.media', ['page' => $page])
            ->call('deleteMedia', $media->id);

        $this->assertModelMissing($media);
    });

    it('does not delete media of another page', function () {
        $otherPage = Page::factory()->create();
        $media = $otherPage->addMedia(UploadedFile::fake()->image('other.jpg'))->toMediaCollection(Page::MediaCollection);

        Livewire::test('pages::admin.pages.media', ['page' => Page::factory()->create()])
            ->call('deleteMedia', $media->id)
            ->assertNotFound();

        $this->assertModelExists($media);
    });

    it('offers the page images in the editor image picker', function () {
        $page = Page::factory()->create();
        $image = $page->addMedia(UploadedFile::fake()->image('picked.jpg'))->toMediaCollection(Page::MediaCollection);
        $page->addMedia(UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'))->toMediaCollection(Page::MediaCollection);

        Livewire::test('pages::admin.pages.edit', ['page' => $page])
            ->call('addBrick', 'hero')
            ->assertSee('picked')
            ->assertDontSee('notes')
            ->set('bricks.0.data.title', 'Welcome')
            ->set('bricks.0.data.image_url', $image->getUrl())
            ->call('save')
            ->assertHasNoErrors();

        expect($page->bricks()->first()->data['image_url'])->toBe($image->getUrl());
    });
});
