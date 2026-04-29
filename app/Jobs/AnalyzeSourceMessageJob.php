<?php

namespace App\Jobs;

use App\Models\SourceMessage;
use App\Models\Task;
use App\Models\TaskEvent;
use App\Services\Ai\AiTaskAnalyzer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeSourceMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $sourceMessageId) {}

    public function handle(AiTaskAnalyzer $analyzer): void
    {
        $message = SourceMessage::with('sourceAccount')->find($this->sourceMessageId);
        if (! $message || $message->ai_processed) return;

        $result = $analyzer->analyzeMessage($message);

        $message->ai_processed = true;
        $message->processed_at = now();

        if ($result->isTask
            && ($result->confidence === null || $result->confidence >= config('inbox.ai.min_confidence', 0.6))) {

            $duplicate = Task::where('source_account_id', $message->source_account_id)
                ->where('source_external_id', $message->external_id)
                ->first();
            if ($duplicate) {
                $message->created_task_id = $duplicate->id;
                $message->save();
                return;
            }

            $task = Task::create([
                'user_id' => $message->sourceAccount->user_id,
                'title' => $result->title ?: ($message->title ?: 'Untitled task'),
                'description' => $result->description,
                'status' => 'inbox',
                'priority' => $result->priority,
                'due_date' => $result->dueDate ?: null,
                'source_type' => $message->source_type,
                'source_account_id' => $message->source_account_id,
                'source_account_label' => $message->sourceAccount?->label(),
                'source_external_id' => $message->external_id,
                'source_parent_external_id' => $message->external_parent_id,
                'source_url' => $message->source_url,
                'context_text' => $message->body_text,
                'ai_summary' => $result->summary,
                'ai_reasoning_short' => $result->reasoningShort,
                'follow_up_suggestion' => $result->followUpSuggestion,
                'ai_confidence' => $result->confidence,
                'external_sync_status' => 'imported_only',
                'needs_review' => true,
            ]);

            TaskEvent::create([
                'task_id' => $task->id,
                'event' => 'created',
                'payload' => ['by' => 'ai', 'provider' => $message->source_type, 'message_id' => $message->id],
            ]);

            $message->created_task_id = $task->id;
        }

        $message->save();
    }
}
