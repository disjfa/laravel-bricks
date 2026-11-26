<?php

use App\Enums\ColorScheme;
use App\Enums\ThemeColor;
use App\Features\Blogs;
use App\Features\Forms;
use App\Features\SiteMenu;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesSeeder::class);
});

it('is only available to admins', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.settings'))
        ->assertForbidden();
});

describe('as admin', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->admin()->create());
    });

    it('starts with the default colors and every feature turned on', function () {
        Livewire::test('pages::admin.settings')
            ->assertSet('theme.light.accent', ThemeColor::Accent->default(ColorScheme::Light))
            ->assertSet('theme.dark.background', ThemeColor::Background->default(ColorScheme::Dark))
            ->assertSet('features', ['Blogs' => true, 'Forms' => true, 'SiteMenu' => true]);
    });

    it('saves the theme colors', function () {
        Livewire::test('pages::admin.settings')
            ->set('theme.light.accent', '#1D4ED8')
            ->set('theme.dark.menubar_background', '#000000')
            ->call('save')
            ->assertHasNoErrors();

        expect(ThemeColor::palette())
            ->light->accent->toBe('#1d4ed8')
            ->dark->menubar_background->toBe('#000000');
    });

    it('rejects a color that is not a hex color', function (string $value) {
        Livewire::test('pages::admin.settings')
            ->set('theme.light.accent', $value)
            ->call('save')
            ->assertHasErrors(['theme.light.accent' => 'Use a hex color like #1d4ed8.']);
    })->with(['red', '#fff', 'red;} body {display:none']);

    it('resets the colors to the defaults', function () {
        Livewire::test('pages::admin.settings')
            ->set('theme.light.accent', '#1d4ed8')
            ->call('resetTheme')
            ->assertSet('theme.light.accent', ThemeColor::Accent->default(ColorScheme::Light));
    });

    it('turns features on and off for the whole site', function () {
        Livewire::test('pages::admin.settings')
            ->set('features.Blogs', false)
            ->set('features.SiteMenu', false)
            ->call('save')
            ->assertHasNoErrors();

        expect(Feature::inactive(Blogs::class))->toBeTrue()
            ->and(Feature::active(Forms::class))->toBeTrue()
            ->and(Feature::inactive(SiteMenu::class))->toBeTrue();

        auth()->logout();

        expect(Feature::inactive(Blogs::class))->toBeTrue();
    });

    it('saves the site title and the default meta title and description', function () {
        Livewire::test('pages::admin.settings')
            ->set('siteTitle', 'Brick Works')
            ->set('metaTitle', 'Brick Works, pages built from bricks')
            ->set('metaDescription', 'We build websites.')
            ->call('save')
            ->assertHasNoErrors();

        Page::factory()->published()->create(['slug' => 'home', 'meta_description' => null]);
        Page::factory()->published()->create(['slug' => 'about', 'title' => 'About', 'meta_description' => 'All about us.']);

        $this->get(route('home'))
            ->assertSeeInOrder(['<title>', 'Brick Works, pages built from bricks', '</title>'], false)
            ->assertSee('<meta name="description" content="We build websites.">', false)
            ->assertSee('Brick Works');

        $this->get('/about')
            ->assertSeeInOrder(['<title>', 'About - Brick Works, pages built from bricks', '</title>'], false)
            ->assertSee('<meta name="description" content="All about us.">', false);
    });

    it('falls back to the app name without a site title', function () {
        Livewire::test('pages::admin.settings')
            ->set('siteTitle', '')
            ->call('save');

        expect(Setting::siteTitle())->toBe(config('app.name'))
            ->and(Setting::metaTitle())->toBe(config('app.name'));
    });

    it('uploads a logo that replaces the default logo', function () {
        Storage::fake('public');

        Livewire::test('pages::admin.settings')
            ->set('logo', UploadedFile::fake()->image('logo.png', 200, 64))
            ->assertHasNoErrors();

        $path = Setting::get(Setting::SiteLogoKey);
        Storage::disk('public')->assertExists($path);

        Page::factory()->published()->create(['slug' => 'home']);

        $this->get(route('home'))->assertSee('src="'.Storage::disk('public')->url($path).'"', false);
    });

    it('replaces and removes the logo file', function () {
        Storage::fake('public');

        $component = Livewire::test('pages::admin.settings')
            ->set('logo', UploadedFile::fake()->image('first.png'));
        $firstPath = Setting::get(Setting::SiteLogoKey);

        $component->set('logo', UploadedFile::fake()->image('second.png'));
        Storage::disk('public')->assertMissing($firstPath);

        $secondPath = Setting::get(Setting::SiteLogoKey);
        $component->call('removeLogo');

        Storage::disk('public')->assertMissing($secondPath);
        expect(Setting::siteLogoUrl())->toBeNull();
    });

    it('rejects an svg logo', function () {
        Storage::fake('public');

        Livewire::test('pages::admin.settings')
            ->set('logo', UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'))
            ->assertHasErrors(['logo']);

        expect(Setting::get(Setting::SiteLogoKey))->toBeNull();
    });
});
