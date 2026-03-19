<?php

namespace Tests\Feature;

use App\Models\Projects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRolesAndPermissions;

class ProjectsTest extends TestCase
{
    use RefreshDatabase, WithRolesAndPermissions;

    public function test_admin_can_list_projects(): void
    {
        $admin = $this->createAdmin();
        Projects::factory()->count(3)->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/projects');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_project(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/projects', [
                'name' => 'Test Project',
                'client_name' => 'Test Client',
                'description' => 'A test project',
                'status' => 'draft',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', ['name' => 'Test Project']);
    }

    public function test_create_project_requires_name_and_client(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/projects', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'client_name']);
    }

    public function test_admin_can_show_project(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $project->name]);
    }

    public function test_admin_can_update_project(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/projects/{$project->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Name']);
    }

    public function test_admin_can_delete_project(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
    }

    public function test_viewer_cannot_create_project(): void
    {
        $viewer = $this->createViewer();

        $response = $this->actingAs($viewer, 'sanctum')
            ->postJson('/api/projects', [
                'name' => 'Test',
                'client_name' => 'Client',
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_projects(): void
    {
        $response = $this->getJson('/api/projects');
        $response->assertStatus(401);
    }

    public function test_create_project_generates_audit_trail(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/projects', [
                'name' => 'Audited Project',
                'client_name' => 'Client',
                'status' => 'draft',
            ]);

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'project',
            'action' => 'created',
            'actor_user_id' => $admin->id,
        ]);
    }
}
