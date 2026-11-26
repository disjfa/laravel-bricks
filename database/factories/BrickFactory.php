<?php

namespace Database\Factories;

use App\Models\Brick;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brick>
 */
class BrickFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brickable_type' => Page::class,
            'brickable_id' => Page::factory(),
            'type' => 'text',
            'data' => ['body' => fake()->paragraph()],
            'position' => 0,
        ];
    }

    public function heading(string $text = 'A heading', int $level = 2): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'heading',
            'data' => ['text' => $text, 'level' => $level],
        ]);
    }
}
