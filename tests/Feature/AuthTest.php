<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRolesAndPermissions;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithRolesAndPermissions;

    public function test_login_with_valid_credentials(): void
    {
        $this->seedRolesAndPermissions();
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $user->assignRole('admin');

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token', 'abilities']);
    }

    public function test_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_admin_can_register_user(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/register', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'secret',
                'roles' => 'viewer',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token', 'abilities']);
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_non_admin_cannot_register_user(): void
    {
        $viewer = $this->createViewer();

        $response = $this->actingAs($viewer, 'sanctum')
            ->postJson('/api/register', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'secret',
                'roles' => 'viewer',
            ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'secret',
            'roles' => 'viewer',
        ]);

        $response->assertStatus(401);
    }
}
