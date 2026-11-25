<?php

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesSeeder::class);
});

describe('index', function () {
    it('lists users with their role', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Jane Visitor']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Jane Visitor')
            ->assertSee($user->email);
    });

    it('filters users by name or email', function () {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Jane Visitor']);
        User::factory()->create(['name' => 'Other Person', 'email' => 'other@example.com']);

        $this->actingAs($admin);

        Livewire::test('pages::admin.users.index')
            ->set('search', 'Jane')
            ->assertSee('Jane Visitor')
            ->assertDontSee('Other Person');
    });

    it('only offers impersonation for users that can be impersonated', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertSee(route('admin.users.impersonate', $user))
            ->assertDontSee(route('admin.users.impersonate', $otherAdmin))
            ->assertDontSee(route('admin.users.impersonate', $admin));
    });
});

describe('impersonation', function () {
    it('lets an admin impersonate a user', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $user))
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        expect(session('impersonated_by'))->toBe($admin->id);
    });

    it('forbids impersonating another admin', function () {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $otherAdmin))
            ->assertForbidden();

        $this->assertAuthenticatedAs($admin);
    });

    it('forbids users without the admin role', function () {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.users.impersonate', $target))
            ->assertForbidden();

        $this->assertAuthenticatedAs($user);
    });

    it('returns to the original admin when leaving', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.impersonate', $user));

        $this->get(route('home'))->assertSee(__('Leave impersonation'));

        $this->delete(route('impersonation.destroy'))
            ->assertRedirect(route('admin.users.index'));

        $this->assertAuthenticatedAs($admin);
    });

    it('forbids leaving when not impersonating', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('impersonation.destroy'))
            ->assertForbidden();
    });
});
