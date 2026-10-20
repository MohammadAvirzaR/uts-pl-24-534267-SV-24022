<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'pm']);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_authenticated_user_can_list_projects(): void
    {
        Project::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson('/api/projects');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_authenticated_user_can_create_project(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/projects', [
                             'name' => 'New Project',
                             'description' => 'A test project',
                             'status' => 'planning',
                             'deadline' => '2027-01-01',
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'New Project');

        $this->assertDatabaseHas('projects', [
            'name' => 'New Project',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_project_validation_error(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->postJson('/api/projects', [
                             // 'name' is required but missing
                             'status' => 'invalid_status',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'status']);
    }

    public function test_authenticated_user_can_show_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $project->id);
    }

    public function test_authenticated_user_can_update_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->putJson("/api/projects/{$project->id}", [
                             'name' => 'Updated Project Name',
                             'status' => 'active',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Updated Project Name');
    }

    public function test_authenticated_user_can_delete_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
                         ->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }
}
