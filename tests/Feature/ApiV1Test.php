<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    protected function allowed(): User
    {
        config(['inbox.allowed_email' => 'allowed@test.dev']);
        return User::factory()->create(['email' => 'allowed@test.dev']);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/v1/tasks')->assertUnauthorized();
    }

    public function test_disallowed_user_token_is_blocked(): void
    {
        $user = User::factory()->create(['email' => 'nope@test.dev']);
        Sanctum::actingAs($user);
        $this->getJson('/api/v1/tasks')->assertForbidden();
    }

    public function test_allowed_user_can_list_and_create_tasks(): void
    {
        Sanctum::actingAs($this->allowed());

        $this->postJson('/api/v1/tasks', ['title' => 'API task', 'priority' => 'urgent'])
            ->assertCreated()
            ->assertJsonPath('data.title', 'API task')
            ->assertJsonPath('data.priority', 'urgent');

        $this->getJson('/api/v1/tasks')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'API task');
    }

    public function test_complete_endpoint_marks_task_done(): void
    {
        $user = $this->allowed();
        Sanctum::actingAs($user);
        $task = Task::create([
            'user_id' => $user->id, 'title' => 'X', 'status' => 'todo',
            'priority' => 'normal', 'source_type' => 'manual',
        ]);

        $this->postJson("/api/v1/tasks/{$task->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'done');

        $this->assertNotNull($task->refresh()->completed_at);
    }

    public function test_dashboard_summary(): void
    {
        Sanctum::actingAs($this->allowed());
        $this->getJson('/api/v1/dashboard/summary')
            ->assertOk()
            ->assertJsonStructure(['open_tasks', 'urgent_tasks', 'recent_tasks']);
    }

    public function test_user_cannot_touch_others_tasks_via_api(): void
    {
        $user = $this->allowed();
        $other = User::factory()->create();
        $task = Task::create([
            'user_id' => $other->id, 'title' => 'Hidden', 'status' => 'todo',
            'priority' => 'normal', 'source_type' => 'manual',
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/tasks/{$task->id}")->assertForbidden();
    }
}
