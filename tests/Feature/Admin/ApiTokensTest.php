<?php

use App\Enums\ApiAbility;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('creates a token that may read content and shows it once', function () {
    $component = Livewire::test('pages::admin.api-tokens')
        ->set('name', 'Mobile app')
        ->call('createToken')
        ->assertHasNoErrors();

    $token = $this->admin->tokens()->sole();

    expect($token->name)->toBe('Mobile app')
        ->and($token->abilities)->toBe([ApiAbility::ContentRead->value])
        ->and($component->get('plainTextToken'))->toStartWith($token->id.'|');

    $this->withToken($component->get('plainTextToken'))->getJson(route('api.v1.pages.index'))->assertOk();
});

it('revokes a token', function () {
    $token = $this->admin->createToken('Old app', [ApiAbility::ContentRead->value]);

    Livewire::test('pages::admin.api-tokens')
        ->assertSee('Old app')
        ->call('revokeToken', $token->accessToken->id);

    expect($this->admin->tokens()->count())->toBe(0);
});

it('does not revoke the token of someone else', function () {
    $otherToken = User::factory()->admin()->create()->createToken('Theirs')->accessToken;

    Livewire::test('pages::admin.api-tokens')
        ->call('revokeToken', $otherToken->id)
        ->assertNotFound();

    $this->assertModelExists($otherToken);
});

describe('documentation', function () {
    it('serves the openapi document to admins', function () {
        $this->getJson(route('scramble.docs.document'))
            ->assertOk()
            ->assertJsonPath('paths./v1/pages.get.tags', ['Pages']);
    });

    it('hides the documentation from users who are not admins', function () {
        $this->actingAs(User::factory()->create())
            ->get(route('scramble.docs.ui'))
            ->assertForbidden();
    });
});
