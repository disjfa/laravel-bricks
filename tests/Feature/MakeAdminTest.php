<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('makes a verified admin, also on an install without roles', function () {
    $this->artisan('app:make:admin', ['--name' => 'Jane Doe', '--email' => 'jane@example.com', '--password' => 'a-good-password'])
        ->assertSuccessful();

    $user = User::query()->sole();

    expect($user->hasRole(UserRole::Admin->value))->toBeTrue()
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and(Hash::check('a-good-password', $user->password))->toBeTrue();
});

it('asks for what is missing', function () {
    $this->artisan('app:make:admin')
        ->expectsQuestion('Email address', 'jane@example.com')
        ->expectsQuestion('Name', 'Jane Doe')
        ->expectsQuestion('Password', 'a-good-password')
        ->assertSuccessful();

    expect(User::query()->sole())->name->toBe('Jane Doe');
});

it('makes an existing user an admin and keeps their password', function () {
    $user = User::factory()->create(['email' => 'jane@example.com', 'password' => 'old-password']);

    $this->artisan('app:make:admin', ['--email' => 'jane@example.com'])
        ->assertSuccessful();

    expect($user->refresh()->hasRole(UserRole::Admin->value))->toBeTrue()
        ->and(Hash::check('old-password', $user->password))->toBeTrue()
        ->and(User::query()->count())->toBe(1);
});

it('refuses an invalid email address or a weak password', function (array $options) {
    $this->artisan('app:make:admin', ['--name' => 'Jane Doe', ...$options])
        ->assertFailed();

    expect(User::query()->count())->toBe(0);
})->with([
    'invalid email' => [['--email' => 'not-an-email', '--password' => 'a-good-password']],
    'short password' => [['--email' => 'jane@example.com', '--password' => 'short']],
]);
