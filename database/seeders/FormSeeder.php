<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    public function run(): void
    {
        if (Form::query()->where('name', 'Contact')->exists()) {
            return;
        }

        Form::factory()->contact()->create([
            'description' => 'Questions or ideas? Send us a message.',
        ]);
    }
}
