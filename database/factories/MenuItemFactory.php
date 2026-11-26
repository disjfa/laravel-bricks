<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'label' => fake()->word(),
            'type' => 'url',
            'data' => ['url' => fake()->url(), 'new_tab' => false],
            'position' => 0,
        ];
    }

    public function url(string $url, bool $newTab = false): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'url',
            'data' => ['url' => $url, 'new_tab' => $newTab],
        ]);
    }

    public function forPage(Page $page): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'page',
            'data' => ['page_id' => $page->id],
        ]);
    }

    public function forBlog(Blog $blog): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'blog',
            'data' => ['blog_id' => $blog->id],
        ]);
    }
}
