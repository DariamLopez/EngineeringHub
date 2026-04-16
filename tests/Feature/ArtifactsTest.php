<?php

namespace Tests\Feature;

use App\Enums\ArtifactStatusEnum;
use App\Enums\ArtifactTypeEnum;
use App\Models\Artifacts;
use App\Models\Projects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRolesAndPermissions;

class ArtifactsTest extends TestCase
{
    use RefreshDatabase, WithRolesAndPermissions;

    public function test_list_artifacts_requires_project_id(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/artifacts');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id']);
    }

    public function test_admin_can_list_artifacts_by_project(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);
        Artifacts::factory()->count(2)->create(['project_id' => $project->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/artifacts?project_id={$project->id}");

        $response->assertStatus(200);
    }

    public function test_admin_can_create_artifact(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/artifacts', [
                'type' => ArtifactTypeEnum::BIG_PICTURE->value,
                'status' => ArtifactStatusEnum::NOT_STARTED->value,
                'project_id' => $project->id,
                'content_json' => [
                    'ecosystem_vision' => 'A vision',
                    'impacted_domains' => ['domain1'],
                    'success_definition' => 'Success',
                ],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('artifacts', ['project_id' => $project->id, 'type' => 'big_picture']);
    }

    public function test_admin_can_list_domain_breakdown_artifact_and_resolve_domain_ids(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);
        $domainA = \App\Models\Domain::factory()->create(['project_id' => $project->id]);
        $domainB = \App\Models\Domain::factory()->create(['project_id' => $project->id]);

        Artifacts::factory()->create([
            'project_id' => $project->id,
            'type' => ArtifactTypeEnum::DOMAIN_BREAKDOWN->value,
            'content_json' => ['domains' => [$domainA->id, $domainB->id]],
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/artifacts?project_id={$project->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonPath('0.content_json.0.id', $domainA->id);
        $response->assertJsonPath('0.content_json.1.id', $domainB->id);
    }

    public function test_create_artifact_validates_content_json_extra_fields(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/artifacts', [
                'type' => ArtifactTypeEnum::BIG_PICTURE->value,
                'status' => ArtifactStatusEnum::NOT_STARTED->value,
                'project_id' => $project->id,
                'content_json' => [
                    'ecosystem_vision' => 'A vision',
                    'impacted_domains' => ['domain1'],
                    'success_definition' => 'Success',
                    'invalid_field' => 'should not be here',
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_viewer_cannot_create_artifact(): void
    {
        $viewer = $this->createViewer();
        $project = Projects::factory()->create();

        $response = $this->actingAs($viewer, 'sanctum')
            ->postJson('/api/artifacts', [
                'type' => ArtifactTypeEnum::BIG_PICTURE->value,
                'status' => ArtifactStatusEnum::NOT_STARTED->value,
                'project_id' => $project->id,
                'content_json' => [
                    'ecosystem_vision' => 'A vision',
                    'impacted_domains' => ['domain1'],
                    'success_definition' => 'Success',
                ],
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_show_artifact(): void
    {
        $admin = $this->createAdmin();
        $artifact = Artifacts::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/artifacts/{$artifact->id}");

        $response->assertStatus(200);
    }

    public function test_create_artifact_generates_audit_trail(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/artifacts', [
                'type' => ArtifactTypeEnum::BIG_PICTURE->value,
                'status' => ArtifactStatusEnum::NOT_STARTED->value,
                'project_id' => $project->id,
                'content_json' => [
                    'ecosystem_vision' => 'A vision',
                    'impacted_domains' => ['domain1'],
                    'success_definition' => 'Success',
                ],
            ]);

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'artifact',
            'action' => 'created',
            'actor_user_id' => $admin->id,
        ]);
    }

    public function test_unauthenticated_cannot_access_artifacts(): void
    {
        $response = $this->getJson('/api/artifacts?project_id=1');
        $response->assertStatus(401);
    }
}
