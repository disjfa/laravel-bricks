<?php

use App\Actions\Pwa\GenerateAppIcons;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

it('serves a web app manifest with the default icons', function () {
    Setting::put(Setting::SiteTitleKey, 'Brick Works Website');

    $this->get(route('manifest'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/manifest+json')
        ->assertJson([
            'name' => 'Brick Works Website',
            'short_name' => 'Brick Works',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'icons' => [
                ['src' => asset('apple-touch-icon.png'), 'sizes' => '180x180'],
            ],
        ]);
});

it('links the manifest, theme color and apple tags on the website', function () {
    Page::factory()->published()->create(['slug' => 'home']);

    $this->get(route('home'))
        ->assertSee('<link rel="manifest" href="'.route('manifest').'">', false)
        ->assertSee('<meta name="theme-color" media="(prefers-color-scheme: light)"', false)
        ->assertSee('<meta name="apple-mobile-web-app-capable" content="yes">', false)
        ->assertSee('<link rel="apple-touch-icon" href="/apple-touch-icon.png">', false);
});

describe('app icon', function () {
    beforeEach(function () {
        $this->seed(RolesSeeder::class);
        $this->actingAs(User::factory()->admin()->create());
    });

    it('creates every icon size from the upload', function () {
        Livewire::test('pages::admin.settings')
            ->set('appIcon', UploadedFile::fake()->image('icon.png', 600, 600))
            ->assertHasNoErrors();

        $directory = Setting::get(Setting::AppIconKey);
        $files = collect([
            ...array_map(fn (int $size) => "icon-{$size}.png", GenerateAppIcons::IconSizes),
            ...array_map(fn (int $size) => "maskable-{$size}.png", GenerateAppIcons::MaskableSizes),
            ...array_map(fn (int $size) => "apple-touch-icon-{$size}.png", GenerateAppIcons::AppleSizes),
            ...array_map(fn (int $size) => "favicon-{$size}.png", GenerateAppIcons::FaviconSizes),
        ]);

        $files->each(fn (string $file) => Storage::disk('public')->assertExists("{$directory}/{$file}"));

        expect(array_slice(getimagesize(Storage::disk('public')->path("{$directory}/icon-512.png")), 0, 2))->toBe([512, 512])
            ->and(array_slice(getimagesize(Storage::disk('public')->path("{$directory}/apple-touch-icon-180.png")), 0, 2))->toBe([180, 180]);

        $manifest = $this->get(route('manifest'))->json();

        expect($manifest['icons'])->toHaveCount(count(GenerateAppIcons::IconSizes) + count(GenerateAppIcons::MaskableSizes))
            ->and(collect($manifest['icons'])->firstWhere('purpose', 'maskable'))
            ->toMatchArray(['src' => Setting::appIconUrl('maskable-192.png'), 'sizes' => '192x192', 'type' => 'image/png']);

        Page::factory()->published()->create(['slug' => 'home']);

        $this->get(route('home'))
            ->assertSee('<link rel="apple-touch-icon" sizes="180x180" href="'.Setting::appIconUrl('apple-touch-icon-180.png').'">', false)
            ->assertSee('<link rel="icon" type="image/png" sizes="32x32" href="'.Setting::appIconUrl('favicon-32.png').'">', false);
    });

    it('rejects icons that are too small or svg', function (UploadedFile $file) {
        Livewire::test('pages::admin.settings')
            ->set('appIcon', $file)
            ->assertHasErrors(['appIcon']);

        expect(Setting::get(Setting::AppIconKey))->toBeNull();
    })->with([
        'too small' => fn () => UploadedFile::fake()->image('small.png', 256, 256),
        'svg' => fn () => UploadedFile::fake()->create('icon.svg', 10, 'image/svg+xml'),
    ]);

    it('replaces and removes the icons', function () {
        $component = Livewire::test('pages::admin.settings')
            ->set('appIcon', UploadedFile::fake()->image('first.png', 512, 512));
        $firstDirectory = Setting::get(Setting::AppIconKey);

        $component->set('appIcon', UploadedFile::fake()->image('second.png', 512, 512));
        Storage::disk('public')->assertMissing($firstDirectory);

        $secondDirectory = Setting::get(Setting::AppIconKey);
        $component->call('removeAppIcon');

        Storage::disk('public')->assertMissing($secondDirectory);
        expect(Setting::appIconUrl('icon-192.png'))->toBeNull();
    });

    it('creates the icons again when the background color changes', function () {
        $component = Livewire::test('pages::admin.settings')
            ->set('appIcon', UploadedFile::fake()->image('icon.png', 512, 512));
        $directory = Setting::get(Setting::AppIconKey);

        $component->set('theme.light.background', '#000000')->call('save')->assertHasNoErrors();

        expect(Setting::get(Setting::AppIconKey))->not->toBe($directory);
        Storage::disk('public')->assertMissing($directory);
        Storage::disk('public')->assertExists(Setting::get(Setting::AppIconKey).'/maskable-512.png');
    });

    it('saves the short name and display mode', function () {
        Livewire::test('pages::admin.settings')
            ->set('appShortName', 'Bricks')
            ->set('appDisplay', 'minimal-ui')
            ->call('save')
            ->assertHasNoErrors();

        $this->get(route('manifest'))->assertJson(['short_name' => 'Bricks', 'display' => 'minimal-ui']);
    });

    it('rejects an unknown display mode', function () {
        Livewire::test('pages::admin.settings')
            ->set('appDisplay', 'fullscreen-hack')
            ->call('save')
            ->assertHasErrors(['appDisplay']);
    });
});
