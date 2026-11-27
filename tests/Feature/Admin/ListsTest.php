<?php

use App\Models\Form;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    $this->actingAs(User::factory()->admin()->create());
});

it('sorts the pages by a column, and flips the direction on a second click', function () {
    Page::factory()->create(['title' => 'Bravo']);
    Page::factory()->create(['title' => 'Alpha']);
    Page::factory()->create(['title' => 'Charlie']);

    Livewire::test('pages::admin.pages.index')
        ->call('sortBy', 'title')
        ->assertSet('sort', 'title')
        ->assertSeeInOrder(['Alpha', 'Bravo', 'Charlie'])
        ->call('sortBy', 'title')
        ->assertSet('sort', '-title')
        ->assertSeeInOrder(['Charlie', 'Bravo', 'Alpha']);
});

it('ignores a sort that is not allowed', function () {
    Page::factory()->create(['title' => 'Only page']);

    Livewire::withQueryParams(['sort' => 'meta_title'])
        ->test('pages::admin.pages.index')
        ->assertOk()
        ->assertSee('Only page');
});

it('searches and sorts the forms by their number of submissions', function () {
    $busy = Form::factory()->create(['name' => 'Contact']);
    $busy->submissions()->createMany([['data' => []], ['data' => []]]);
    Form::factory()->create(['name' => 'Contest']);
    Form::factory()->create(['name' => 'Newsletter']);

    Livewire::test('pages::admin.forms.index')
        ->set('search', 'Con')
        ->call('sortBy', 'submissions')
        ->call('sortBy', 'submissions')
        ->assertSeeInOrder(['Contact', 'Contest'])
        ->assertDontSee('Newsletter');
});
