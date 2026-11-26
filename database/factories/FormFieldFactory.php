<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = fake()->unique()->word();

        return [
            'form_id' => Form::factory(),
            'type' => 'text',
            'name' => Str::snake($label),
            'settings' => ['label' => Str::ucfirst($label), 'required' => false],
            'position' => 0,
        ];
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'settings' => [...$attributes['settings'], 'required' => true],
        ]);
    }

    /**
     * @param  array<int, string>  $options
     */
    public function select(array $options): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'select',
            'settings' => [...$attributes['settings'], 'options' => implode("\n", $options)],
        ]);
    }

    public function checkbox(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'checkbox',
        ]);
    }
}
