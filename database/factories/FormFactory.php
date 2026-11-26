<?php

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => null,
            'submit_label' => null,
            'success_message' => null,
        ];
    }

    /**
     * A contact form with a required name, a required email and an optional message.
     */
    public function contact(): static
    {
        return $this
            ->state(fn (array $attributes) => ['name' => 'Contact'])
            ->afterCreating(function (Form $form) {
                $form->fields()->createMany([
                    ['type' => 'text', 'name' => 'name', 'settings' => ['label' => 'Name', 'required' => true], 'position' => 0],
                    ['type' => 'email', 'name' => 'email', 'settings' => ['label' => 'Email', 'required' => true], 'position' => 1],
                    ['type' => 'textarea', 'name' => 'message', 'settings' => ['label' => 'Message', 'required' => false], 'position' => 2],
                ]);
            });
    }
}
