<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Model events stay on: slugs, sequence numbers and tags rely on them.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UserSeeder::class,
            FormSeeder::class,
            BlogSeeder::class,
            PageSeeder::class,
            MenuSeeder::class,
        ]);
    }
}
