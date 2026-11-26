<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::ucfirst($name),
            'handle' => Str::slug($name),
        ];
    }

    public function main(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Main menu',
            'handle' => Menu::MainHandle,
        ]);
    }
}
