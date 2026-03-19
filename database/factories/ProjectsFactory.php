<?php

namespace Database\Factories;

use App\Enums\ProjectStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Projects>
 */
class ProjectsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'client_name' => fake()->company(),
            'description' => fake()->paragraph(),
            'status' => ProjectStatusEnum::DRAFT->value,
            'created_by' => \App\Models\User::factory(),
            'is_archived' => false,
        ];
    }
}
