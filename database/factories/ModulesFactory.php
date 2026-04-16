<?php

namespace Database\Factories;

use App\Enums\ModuleStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules>
 */
class ModulesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'status' => ModuleStatusEnum::DRAFT->value,
            'objective' => fake()->sentence(),
            'inputs' => [],
            'outputs' => [],
            'data_structure' => [],
            'logic_rules' => fake()->sentence(),
            'responsibility' => fake()->sentence(),
            'failure_scenarios' => fake()->sentence(),
            'audit_trail_requirements' => fake()->sentence(),
            'dependencies' => [],
            'version_note' => fake()->sentence(),
            'domain_id' => \App\Models\Domain::factory(),
            'project_id' => \App\Models\Projects::factory(),
        ];
    }
}
