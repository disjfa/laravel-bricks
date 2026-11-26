<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'meta_description' => fake()->sentence(),
            'status' => PublishStatus::Concept,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublishStatus::Active,
            'published_at' => now()->subDay(),
        ]);
    }
}
