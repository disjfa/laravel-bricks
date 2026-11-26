<?php

namespace Database\Seeders;

use App\Models\Blog;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        if (Blog::query()->exists()) {
            return;
        }

        foreach (range(1, 8) as $daysAgo) {
            Blog::factory()->published()->create([
                'published_at' => now()->subDays($daysAgo * 3),
            ]);
        }

        Blog::factory()->create(['title' => 'A blog that is still a concept']);
    }
}
