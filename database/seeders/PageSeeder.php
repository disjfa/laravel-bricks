<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $this->createPage('Home', 'home', [
            ['hero', [
                'title' => 'Build pages brick by brick',
                'subtitle' => 'Compose pages in the admin from reusable bricks and publish them in seconds.',
                'button_label' => 'Read more',
                'button_url' => '/about',
            ]],
            ['heading', ['text' => 'What is this?', 'level' => 2]],
            ['text', ['body' => "Every page is a stack of **bricks**. Add, reorder and edit them in the admin.\n\nThe website renders the same bricks with a nicer look."]],
        ]);

        $this->createPage('About', 'about', [
            ['heading', ['text' => 'About us', 'level' => 2]],
            ['text', ['body' => 'We like small, composable building blocks.']],
            ['quote', ['quote' => 'Simplicity is the ultimate sophistication.', 'citation' => 'Leonardo da Vinci']],
            ['divider', []],
            ['button', ['label' => 'Back home', 'url' => '/', 'variant' => 'outline']],
        ]);
    }

    /**
     * @param  array<int, array{0: string, 1: array<string, mixed>}>  $bricks
     */
    protected function createPage(string $title, string $slug, array $bricks): void
    {
        $page = Page::query()->firstOrCreate(
            ['slug' => $slug],
            ['title' => $title, 'published_at' => now()],
        );

        if ($page->bricks()->exists()) {
            return;
        }

        foreach ($bricks as $position => [$type, $data]) {
            $page->bricks()->create([
                'type' => $type,
                'data' => $data,
                'position' => $position,
            ]);
        }
    }
}
