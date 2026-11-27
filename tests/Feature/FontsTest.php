<?php

use App\Enums\SiteFont;
use App\Models\Page;
use App\Models\User;
use App\Settings\FontSettings;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;

beforeEach(function () {
    Page::factory()->published()->create(['slug' => 'home']);
});

it('uses the system font and loads nothing without a choice', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('fonts.bunny.net', false)
        ->assertSee('--site-font-text: '.SiteFont::SystemSans.';', false)
        ->assertSee('--site-font-heading: '.SiteFont::SystemSans.';', false);
});

it('loads the chosen fonts from bunny fonts', function () {
    updateSettings(FontSettings::class, ['text' => SiteFont::Inter]);
    updateSettings(FontSettings::class, ['heading' => SiteFont::PlayfairDisplay]);

    $this->get(route('home'))
        ->assertSee('<link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:400,500,600,700&amp;display=swap">', false)
        ->assertSee('--site-font-text: '.SiteFont::Inter->cssFamily().';', false)
        ->assertSee('--site-font-heading: '.SiteFont::PlayfairDisplay->cssFamily().';', false);
});

it('uses the text font for headings without a heading font', function () {
    updateSettings(FontSettings::class, ['text' => SiteFont::Lora]);

    $this->get(route('home'))
        ->assertSee('family=lora:400,500,600,700&amp;display=swap', false)
        ->assertSee('--site-font-heading: '.SiteFont::Lora->cssFamily().';', false);
});

it('loads a font only once when text and headings use the same font', function () {
    updateSettings(FontSettings::class, ['text' => SiteFont::Figtree]);
    updateSettings(FontSettings::class, ['heading' => SiteFont::Figtree]);

    expect(app(FontSettings::class)->stylesheetUrl())->toBe('https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap');
});

it('uses the same fonts in the admin', function () {
    updateSettings(FontSettings::class, ['text' => SiteFont::Inter]);
    updateSettings(FontSettings::class, ['heading' => SiteFont::Fraunces]);
    $this->seed(RolesSeeder::class);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('<body class="min-h-screen" data-site-theme>', false)
        ->assertSee('family=inter:400,500,600,700|fraunces:400,500,600,700', false)
        ->assertSee('--site-font-heading: '.SiteFont::Fraunces->cssFamily().';', false);
});

describe('settings', function () {
    beforeEach(function () {
        $this->seed(RolesSeeder::class);
        $this->actingAs(User::factory()->admin()->create());
    });

    it('saves the fonts and clears them again', function () {
        Livewire::test('pages::admin.settings')
            ->assertSet('fontText', '')
            ->set('fontText', SiteFont::DmSans->value)
            ->set('fontHeading', SiteFont::Fraunces->value)
            ->call('save')
            ->assertHasNoErrors();

        expect(app(FontSettings::class)->text)->toBe(SiteFont::DmSans)
            ->and(app(FontSettings::class)->heading)->toBe(SiteFont::Fraunces);

        Livewire::test('pages::admin.settings')
            ->assertSet('fontHeading', SiteFont::Fraunces->value)
            ->set('fontText', '')
            ->set('fontHeading', '')
            ->call('save')
            ->assertHasNoErrors();

        expect(app(FontSettings::class)->text)->toBeNull()
            ->and(app(FontSettings::class)->heading)->toBeNull();
    });

    it('rejects a font that is not in the list', function () {
        Livewire::test('pages::admin.settings')
            ->set('fontText', 'comic-sans')
            ->call('save')
            ->assertHasErrors(['fontText']);
    });
});
