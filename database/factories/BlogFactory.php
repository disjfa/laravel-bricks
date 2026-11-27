<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(12),
            'image_url' => null,
            'meta_description' => fake()->sentence(),
            'status' => PublishStatus::Concept,
            'published_at' => null,
            'published_until' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublishStatus::Active,
            'published_at' => now()->subDay(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublishStatus::Closed,
        ]);
    }
}
