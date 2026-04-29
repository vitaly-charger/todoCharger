<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeSourceMessageJob;
use App\Models\SourceAccount;
use App\Models\SourceMessage;
use App\Models\Task;
use App\Models\User;
use App\Services\Ai\AiAnalysisResult;
use App\Services\Ai\AiTaskAnalyzer;
use Mockery;
use Tests\TestCase;

class AnalyzeSourceMessageJobTest extends TestCase
{
    public function test_job_creates_task_from_high_confidence_result(): void
    {
        $user = User::factory()->create();
        $account = SourceAccount::create([
            'user_id' => $user->id, 'type' => 'gmail', 'name' => 'g', 'enabled' => true,
        ]);
        $msg = SourceMessage::create([
            'source_account_id' => $account->id,
            'source_type' => 'gmail',
            'external_id' => 'msg-1',
            'title' => 'Please send report',
            'body_text' => 'Need by Friday',
        ]);

        $analyzer = Mockery::mock(AiTaskAnalyzer::class);
        $analyzer->shouldReceive('analyzeMessage')->once()->andReturn(
            AiAnalysisResult::fromArray([
                'is_task' => true,
                'priority' => 'high',
                'summary' => 'Send report',
                'reasoning_short' => 'Direct ask',
                'follow_up_suggestion' => 'Reply with ETA',
                'confidence' => 0.9,
                'due_date' => null,
                'title' => 'Send report',
            ])
        );
        $this->app->instance(AiTaskAnalyzer::class, $analyzer);

        (new AnalyzeSourceMessageJob($msg->id))->handle($analyzer);

        $this->assertDatabaseHas('tasks', [
            'source_account_id' => $account->id,
            'source_external_id' => 'msg-1',
            'priority' => 'high',
        ]);
    }

    public function test_job_does_not_duplicate_tasks_for_same_message(): void
    {
        $user = User::factory()->create();
        $account = SourceAccount::create([
            'user_id' => $user->id, 'type' => 'gmail', 'name' => 'g', 'enabled' => true,
        ]);
        $msg = SourceMessage::create([
            'source_account_id' => $account->id,
            'source_type' => 'gmail',
            'external_id' => 'msg-2',
            'title' => 'X',
        ]);
        Task::create([
            'user_id' => $user->id,
            'title' => 'existing',
            'source_type' => 'gmail',
            'source_account_id' => $account->id,
            'source_external_id' => 'msg-2',
            'status' => 'todo',
            'priority' => 'normal',
        ]);

        $analyzer = Mockery::mock(AiTaskAnalyzer::class);
        $analyzer->shouldReceive('analyzeMessage')->once()->andReturn(
            AiAnalysisResult::fromArray([
                'is_task' => true, 'priority' => 'normal', 'summary' => 's', 'reasoning_short' => 'r',
                'follow_up_suggestion' => '', 'confidence' => 0.95, 'due_date' => null, 'title' => 'X',
            ])
        );
        $this->app->instance(AiTaskAnalyzer::class, $analyzer);

        (new AnalyzeSourceMessageJob($msg->id))->handle($analyzer);

        $this->assertSame(1, Task::where('source_external_id', 'msg-2')->count());
    }

    public function test_job_does_not_create_task_when_not_a_task(): void
    {
        $user = User::factory()->create();
        $account = SourceAccount::create([
            'user_id' => $user->id, 'type' => 'slack', 'name' => 's', 'enabled' => true,
        ]);
        $msg = SourceMessage::create([
            'source_account_id' => $account->id, 'source_type' => 'slack', 'external_id' => 'm', 'title' => 'hi',
        ]);

        $analyzer = Mockery::mock(AiTaskAnalyzer::class);
        $analyzer->shouldReceive('analyzeMessage')->once()->andReturn(
            AiAnalysisResult::fromArray([
                'is_task' => false, 'priority' => 'low', 'summary' => '', 'reasoning_short' => '',
                'follow_up_suggestion' => '', 'confidence' => 0.95, 'due_date' => null, 'title' => '',
            ])
        );
        $this->app->instance(AiTaskAnalyzer::class, $analyzer);

        (new AnalyzeSourceMessageJob($msg->id))->handle($analyzer);

        $this->assertDatabaseMissing('tasks', ['source_account_id' => $account->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
