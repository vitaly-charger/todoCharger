<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    protected function allowedUser(): User
    {
        config(['inbox.allowed_email' => 'allowed@test.dev']);
        return User::factory()->create(['email' => 'allowed@test.dev']);
    }

    public function test_user_can_create_a_manual_task(): void
    {
        $user = $this->allowedUser();

        $this->actingAs($user)->post('/tasks', [
            'title' => 'Buy milk',
            'description' => 'Skim',
            'priority' => 'high',
        ])->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Buy milk',
            'source_type' => 'manual',
            'priority' => 'high',
        ]);
    }

    public function test_user_can_update_status_and_completed_at_is_set(): void
    {
        $user = $this->allowedUser();
        $task = Task::create([
            'user_id' => $user->id,
            'title' => 'X',
            'status' => 'todo',
            'priority' => 'normal',
            'source_type' => 'manual',
        ]);

        $this->actingAs($user)
            ->patch("/tasks/{$task->id}", ['title' => 'X', 'status' => 'done'])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame('done', $task->status);
        $this->assertNotNull($task->completed_at);
    }

    public function test_user_cannot_view_others_tasks(): void
    {
        $user = $this->allowedUser();
        $other = User::factory()->create();
        $task = Task::create([
            'user_id' => $other->id,
            'title' => 'Hidden',
            'status' => 'todo',
            'priority' => 'normal',
            'source_type' => 'manual',
        ]);

        $this->actingAs($user)->get("/tasks/{$task->id}")->assertForbidden();
    }
}
