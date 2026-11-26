<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'data' => ['message' => fake()->sentence()],
            'ip_address' => fake()->ipv4(),
        ];
    }
}
