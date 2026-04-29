<?php

namespace App\Services\Ai;

use App\Models\AiAnalysisLog;
use App\Models\SourceMessage;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiTaskAnalyzer
{
    public function __construct(protected AiProvider $provider) {}

    public function analyzeMessage(SourceMessage $message): AiAnalysisResult
    {
        $system = $this->systemPrompt();
        $user = $this->buildUserPrompt($message);
        $promptHash = hash('sha256', $system . "\n----\n" . $user);

        try {
            $result = $this->provider->analyze($system, $user);

            AiAnalysisLog::create([
                'source_message_id' => $message->id,
                'provider' => $this->provider->name(),
                'model' => $this->provider->model(),
                'prompt_hash' => $promptHash,
                'response' => $result->raw,
                'is_task' => $result->isTask,
                'confidence' => $result->confidence,
            ]);

            return $result;
        } catch (Throwable $e) {
            Log::error('AI analysis failed', ['err' => $e->getMessage(), 'message_id' => $message->id]);
            AiAnalysisLog::create([
                'source_message_id' => $message->id,
                'provider' => $this->provider->name(),
                'model' => $this->provider->model(),
                'prompt_hash' => $promptHash,
                'error_message' => $e->getMessage(),
            ]);
            return new AiAnalysisResult(false, reasoningShort: 'AI failure: ' . $e->getMessage());
        }
    }

    public function reanalyzeTask(Task $task): AiAnalysisResult
    {
        $system = $this->systemPrompt();
        $user = "Re-analyze this existing task:\n"
            . "Title: {$task->title}\n"
            . "Description: " . ($task->description ?? '') . "\n"
            . "Original context:\n" . ($task->context_text ?? '');
        $promptHash = hash('sha256', $system . "\n----\n" . $user);

        try {
            $result = $this->provider->analyze($system, $user);
            AiAnalysisLog::create([
                'task_id' => $task->id,
                'provider' => $this->provider->name(),
                'model' => $this->provider->model(),
                'prompt_hash' => $promptHash,
                'response' => $result->raw,
                'is_task' => $result->isTask,
                'confidence' => $result->confidence,
            ]);
            return $result;
        } catch (Throwable $e) {
            AiAnalysisLog::create([
                'task_id' => $task->id,
                'provider' => $this->provider->name(),
                'model' => $this->provider->model(),
                'error_message' => $e->getMessage(),
            ]);
            return new AiAnalysisResult(false, reasoningShort: 'AI failure: ' . $e->getMessage());
        }
    }

    protected function systemPrompt(): string
    {
        return <<<PROMPT
You are an assistant that scans incoming messages from email, Slack, Telegram,
monday.com, and Wrike and decides if each one is an actionable task for the user.

Return STRICT JSON with this exact shape (no prose, no markdown):
{
  "is_task": true|false,
  "title": "short imperative title",
  "description": "1-3 sentence description",
  "priority": "low|normal|high|urgent",
  "due_date": "YYYY-MM-DD or null",
  "summary": "AI summary of the message",
  "reasoning_short": "one sentence explaining the decision",
  "follow_up_suggestion": "one sentence suggested next action",
  "confidence": 0.0-1.0
}

Rules:
- FYI broadcasts, newsletters, marketing, spam, automated notifications => is_task=false.
- Anything that needs a reply, decision, approval, fix, payment, scheduling,
  delivery, review, or follow-up by the user => is_task=true.
- Be conservative; if unsure, set is_task=false and confidence below 0.5.
- Use the source's own deadlines if any; do not invent dates.
PROMPT;
    }

    protected function buildUserPrompt(SourceMessage $message): string
    {
        $body = config('inbox.ai.store_raw_body', true)
            ? (string) $message->body_text
            : substr((string) $message->body_text, 0, 800);

        return "Source: {$message->source_type}\n"
            . "Account: " . ($message->sourceAccount?->label() ?? 'unknown') . "\n"
            . "Sender: " . ($message->sender_name ?? 'unknown')
            . " <" . ($message->sender_identifier ?? '') . ">\n"
            . "Subject/Title: " . ($message->title ?? '(none)') . "\n"
            . "Received: " . ($message->received_at?->toIso8601String() ?? 'n/a') . "\n"
            . "----\n"
            . $body;
    }
}
