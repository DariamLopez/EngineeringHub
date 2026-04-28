<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Modules;
use App\Models\Projects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRolesAndPermissions;

class ModulesTest extends TestCase
{
    use RefreshDatabase, WithRolesAndPermissions;

    public function test_admin_can_list_modules(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/modules');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_module(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);
        $domain = Domain::factory()->create(['project_id' => $project->id, 'owner_user_id' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/modules', [
                'name' => 'Auth Module',
                'status' => 'draft',
                'project_id' => $project->id,
                'domain_id' => $domain->id,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('modules', ['name' => 'Auth Module']);
    }

    public function test_create_module_requires_name(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/modules', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_viewer_cannot_create_module(): void
    {
        $viewer = $this->createViewer();
        $project = Projects::factory()->create();
        $domain = Domain::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($viewer, 'sanctum')
            ->postJson('/api/v1/modules', [
                'name' => 'Module',
                'status' => 'draft',
                'project_id' => $project->id,
                'domain_id' => $domain->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_create_module_generates_audit_trail(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);
        $domain = Domain::factory()->create(['project_id' => $project->id, 'owner_user_id' => $admin->id]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/modules', [
                'name' => 'Audited Module',
                'status' => 'draft',
                'project_id' => $project->id,
                'domain_id' => $domain->id,
            ]);

        $this->assertDatabaseHas('audit_trails', [
            'entity_type' => 'module',
            'action' => 'created',
            'actor_user_id' => $admin->id,
        ]);
    }

    public function test_unauthenticated_cannot_access_modules(): void
    {
        $response = $this->getJson('/api/v1/modules');
        $response->assertStatus(401);
    }
}
