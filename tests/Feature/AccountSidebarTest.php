<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;

it('has no dashboard route anymore', function () {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertNotFound();
});

it('shows admins a link to the admin in the account sidebar', function () {
    $this->seed(RolesSeeder::class);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('data-test="admin-link"', false);
});

it('does not show the admin link to users without the admin role', function () {
    $this->seed(RolesSeeder::class);

    $this->actingAs(User::factory()->create())
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('data-test="admin-link"', false);
});
