<?php

namespace Database\Factories;

use App\Enums\ArtifactStatusEnum;
use App\Enums\ArtifactTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Artifacts>
 */
class ArtifactsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => ArtifactTypeEnum::BIG_PICTURE->value,
            'status' => ArtifactStatusEnum::NOT_STARTED->value,
            'owner_user_id' => \App\Models\User::factory(),
            'project_id' => \App\Models\Projects::factory(),
            'content_json' => [
                'ecosystem_vision' => fake()->sentence(),
                'impacted_domains' => [fake()->word()],
                'success_definition' => fake()->sentence(),
            ],
        ];
    }
}
