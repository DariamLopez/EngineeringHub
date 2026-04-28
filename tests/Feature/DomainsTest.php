<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Projects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRolesAndPermissions;

class DomainsTest extends TestCase
{
    use RefreshDatabase, WithRolesAndPermissions;

    public function test_authenticated_user_can_list_domains(): void
    {
        $admin = $this->createAdmin();
        Domain::factory()->count(2)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/domains');

        $response->assertStatus(200);
    }

    public function test_can_create_domain(): void
    {
        $admin = $this->createAdmin();
        $project = Projects::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/domains', [
                'name' => 'Auth Domain',
                'objective' => 'Handle authentication',
                'owner_user_id' => $admin->id,
                'project_id' => $project->id,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('domains', ['name' => 'Auth Domain']);
    }

    public function test_create_domain_requires_fields(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/domains', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'objective', 'owner_user_id', 'project_id']);
    }

    public function test_can_show_domain(): void
    {
        $admin = $this->createAdmin();
        $domain = Domain::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/domains/{$domain->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $domain->name]);
    }

    public function test_unauthenticated_cannot_access_domains(): void
    {
        $response = $this->getJson('/api/v1/domains');
        $response->assertStatus(401);
    }
}
