<?php

namespace Database\Factories;

use App\Enums\RevisionAction;
use App\Models\Page;
use App\Models\Revision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Revision>
 */
class RevisionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'revisionable_type' => Page::class,
            'revisionable_id' => Page::factory(),
            'user_id' => null,
            'action' => RevisionAction::Updated,
            'snapshot' => [],
        ];
    }
}
