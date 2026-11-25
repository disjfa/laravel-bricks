<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * All seeded users get the password "password".
     */
    public function run(): void
    {
        User::factory()
            ->withRole(UserRole::Admin)
            ->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);

        User::factory()
            ->withRole(UserRole::User)
            ->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        User::factory()
            ->count(20)
            ->withRole(UserRole::User)
            ->create();
    }
}
