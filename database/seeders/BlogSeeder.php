<?php

namespace Database\Seeders;

use App\Enums\BlogTagType;
use App\Models\Blog;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        if (Blog::query()->exists()) {
            return;
        }

        $categories = ['News', 'Tutorials', 'Releases'];
        $tags = ['laravel', 'livewire', 'bricks', 'design', 'api'];

        foreach (range(1, 8) as $daysAgo) {
            $blog = Blog::factory()->published()->create([
                'published_at' => now()->subDays($daysAgo * 3),
            ]);

            $blog->attachTag($categories[$daysAgo % count($categories)], BlogTagType::Category->value);
            $blog->attachTags(array_slice($tags, $daysAgo % 3, 2), BlogTagType::Tag->value);
        }

        Blog::factory()->create(['title' => 'A blog that is still a concept']);
    }
}
