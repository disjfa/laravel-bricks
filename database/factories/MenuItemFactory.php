<?php

namespace Database\Factories;

use App\Enums\MenuItemType;
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
            'type' => MenuItemType::Url,
            'url' => fake()->url(),
            'position' => 0,
        ];
    }

    public function forPage(Page $page): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MenuItemType::Page,
            'page_id' => $page->id,
            'url' => null,
        ]);
    }

    public function forBlog(Blog $blog): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MenuItemType::Blog,
            'blog_id' => $blog->id,
            'url' => null,
        ]);
    }
}
