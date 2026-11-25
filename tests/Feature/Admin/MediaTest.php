<?php

use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(RolesSeeder::class);

    $this->actingAs(User::factory()->admin()->create());
});

function attachMedia(Page $page, UploadedFile $file): Media
{
    return $page->addMedia($file)->toMediaCollection(Page::MediaCollection);
}

it('lists the files of all pages with the page they belong to', function () {
    $home = Page::factory()->create(['title' => 'Home page']);
    $about = Page::factory()->create(['title' => 'About page']);
    attachMedia($home, UploadedFile::fake()->image('home-hero.jpg'));
    attachMedia($about, UploadedFile::fake()->create('about-brochure.pdf', 10, 'application/pdf'));

    $this->get(route('admin.media.index'))
        ->assertOk()
        ->assertSee('home-hero')
        ->assertSee('about-brochure')
        ->assertSee('Home page')
        ->assertSee('About page')
        ->assertSee(route('admin.pages.media', $home));
});

it('filters files by type', function () {
    $page = Page::factory()->create();
    attachMedia($page, UploadedFile::fake()->image('photo.jpg'));
    attachMedia($page, UploadedFile::fake()->create('report.pdf', 10, 'application/pdf'));

    Livewire::test('pages::admin.media.index')
        ->set('type', 'images')
        ->assertSee('photo')
        ->assertDontSee('report')
        ->set('type', 'documents')
        ->assertSee('report')
        ->assertDontSee('photo');
});

it('searches files by name', function () {
    $page = Page::factory()->create();
    attachMedia($page, UploadedFile::fake()->image('sunset.jpg'));
    attachMedia($page, UploadedFile::fake()->image('mountain.jpg'));

    Livewire::test('pages::admin.media.index')
        ->set('search', 'sun')
        ->assertSee('sunset')
        ->assertDontSee('mountain');
});

it('deletes a file', function () {
    $media = attachMedia(Page::factory()->create(), UploadedFile::fake()->image('old.jpg'));

    Livewire::test('pages::admin.media.index')
        ->call('deleteMedia', $media->id);

    $this->assertModelMissing($media);
    Storage::disk('public')->assertMissing($media->getPathRelativeToRoot());
});
