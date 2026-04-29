<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Jobs\PushTaskToProviderJob;
use App\Models\Task;
use App\Models\TaskEvent;
use App\Services\Ai\AiTaskAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tasks = Task::query()
            ->where('user_id', $request->user()->id)
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->priority, fn ($q, $v) => $q->where('priority', $v))
            ->when($request->source_type, fn ($q, $v) => $q->where('source_type', $v))
            ->when($request->source_account_id, fn ($q, $v) => $q->where('source_account_id', $v))
            ->when($request->external_sync_status, fn ($q, $v) => $q->where('external_sync_status', $v))
            ->when($request->search, fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->when($request->due_from, fn ($q, $v) => $q->whereDate('due_date', '>=', $v))
            ->when($request->due_to, fn ($q, $v) => $q->whereDate('due_date', '<=', $v))
            ->latest()->paginate(min(100, (int) $request->get('per_page', 25)));

        return TaskResource::collection($tasks);
    }

    public function store(Request $request): TaskResource
    {
        $data = $this->validateData($request);
        $data['user_id'] = $request->user()->id;
        $data['source_type'] = $data['source_type'] ?? 'manual';
        $task = Task::create($data);
        TaskEvent::create(['task_id' => $task->id, 'user_id' => $request->user()->id, 'event' => 'created', 'payload' => ['by' => 'api']]);
        return new TaskResource($task);
    }

    public function show(Request $request, Task $task): TaskResource
    {
        $this->authorize($request, $task);
        $task->load('externalMappings');
        return new TaskResource($task);
    }

    public function update(Request $request, Task $task): TaskResource
    {
        $this->authorize($request, $task);
        $data = $this->validateData($request, partial: true);
        $oldStatus = $task->status;
        $task->fill($data);
        if ($task->isDirty('status') && $task->status === 'done' && ! $task->completed_at) {
            $task->completed_at = now();
        }
        $task->save();
        if ($oldStatus !== $task->status) {
            TaskEvent::create(['task_id' => $task->id, 'user_id' => $request->user()->id, 'event' => 'status_changed', 'payload' => ['from' => $oldStatus, 'to' => $task->status]]);
        }
        return new TaskResource($task);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize($request, $task);
        $task->delete();
        return response()->json(['ok' => true]);
    }

    public function complete(Request $request, Task $task): TaskResource
    {
        $this->authorize($request, $task);
        $task->update(['status' => 'done', 'completed_at' => now()]);
        return new TaskResource($task);
    }

    public function ignore(Request $request, Task $task): TaskResource
    {
        $this->authorize($request, $task);
        $task->update(['status' => 'ignored']);
        return new TaskResource($task);
    }

    public function reopen(Request $request, Task $task): TaskResource
    {
        $this->authorize($request, $task);
        $task->update(['status' => 'todo', 'completed_at' => null]);
        return new TaskResource($task);
    }

    public function reanalyze(Request $request, Task $task, AiTaskAnalyzer $analyzer): TaskResource
    {
        $this->authorize($request, $task);
        $result = $analyzer->reanalyzeTask($task);
        if ($result->isTask) {
            $task->update([
                'ai_summary' => $result->summary,
                'ai_reasoning_short' => $result->reasoningShort,
                'follow_up_suggestion' => $result->followUpSuggestion,
                'ai_confidence' => $result->confidence,
                'priority' => $result->priority,
            ]);
        }
        return new TaskResource($task);
    }

    public function pushToMonday(Request $request, Task $task): JsonResponse
    {
        $this->authorize($request, $task);
        $request->validate(['source_account_id' => 'required|exists:source_accounts,id']);
        PushTaskToProviderJob::dispatch($task->id, (int) $request->source_account_id, 'monday');
        return response()->json(['queued' => true]);
    }

    public function pushToWrike(Request $request, Task $task): JsonResponse
    {
        $this->authorize($request, $task);
        $request->validate(['source_account_id' => 'required|exists:source_accounts,id']);
        PushTaskToProviderJob::dispatch($task->id, (int) $request->source_account_id, 'wrike');
        return response()->json(['queued' => true]);
    }

    protected function authorize(Request $request, Task $task): void
    {
        abort_if($task->user_id !== $request->user()->id, 403);
    }

    protected function validateData(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'title' => ($partial ? 'sometimes|' : '') . 'required|string|max:500',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:' . implode(',', Task::STATUSES),
            'priority' => 'sometimes|in:' . implode(',', Task::PRIORITIES),
            'due_date' => 'nullable|date',
            'source_type' => 'sometimes|in:' . implode(',', Task::SOURCE_TYPES),
            'source_account_id' => 'nullable|exists:source_accounts,id',
        ]);
    }
}
