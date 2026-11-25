<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;

test('guests are redirected to the login page', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});

test('admins see a link to the admin on the team dashboard', function () {
    $this->seed(RolesSeeder::class);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-test="admin-link"', false);
});

test('users without the admin role do not see the admin link', function () {
    $this->seed(RolesSeeder::class);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('data-test="admin-link"', false);
});
