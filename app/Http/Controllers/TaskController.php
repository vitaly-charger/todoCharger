<?php

namespace App\Http\Controllers;

use App\Jobs\PushTaskToProviderJob;
use App\Models\SourceAccount;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskEvent;
use App\Services\Ai\AiTaskAnalyzer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $tasks = Task::query()
            ->where('user_id', $request->user()->id)
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->priority, fn ($q, $v) => $q->where('priority', $v))
            ->when($request->source_type, fn ($q, $v) => $q->where('source_type', $v))
            ->when($request->source_account_id, fn ($q, $v) => $q->where('source_account_id', $v))
            ->when($request->search, fn ($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('title', 'like', "%{$v}%")->orWhere('description', 'like', "%{$v}%");
            }))
            ->when($request->due_from, fn ($q, $v) => $q->whereDate('due_date', '>=', $v))
            ->when($request->due_to, fn ($q, $v) => $q->whereDate('due_date', '<=', $v))
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 WHEN 'low' THEN 4 END")
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['status','priority','source_type','source_account_id','search','due_from','due_to']),
            'sourceAccounts' => SourceAccount::where('user_id', $request->user()->id)
                ->select('id','type','name','identifier')->get(),
        ]);
    }

    public function show(Task $task): Response
    {
        $this->authorizeTask($task);
        $task->load(['comments.user', 'events.user', 'externalMappings', 'sourceAccount']);
        return Inertia::render('Tasks/Show', ['task' => $task]);
    }

    public function create(): Response
    {
        return Inertia::render('Tasks/Create', [
            'sourceAccounts' => SourceAccount::where('user_id', auth()->id())
                ->where('enabled', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['user_id'] = $request->user()->id;
        $data['source_type'] = 'manual';
        $task = Task::create($data);
        TaskEvent::create(['task_id' => $task->id, 'user_id' => $request->user()->id, 'event' => 'created', 'payload' => ['by' => 'user']]);
        return redirect()->route('tasks.show', $task);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($task);
        $data = $this->validateData($request, partial: true);

        $oldStatus = $task->status;
        $task->fill($data);
        if ($task->isDirty('status') && $task->status === 'done') {
            $task->completed_at = now();
        }
        $task->save();

        if ($oldStatus !== $task->status) {
            TaskEvent::create([
                'task_id' => $task->id, 'user_id' => $request->user()->id,
                'event' => 'status_changed', 'payload' => ['from' => $oldStatus, 'to' => $task->status],
            ]);
        }
        return back();
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorizeTask($task);
        $task->delete();
        return redirect()->route('tasks.index');
    }

    public function reanalyze(Task $task, AiTaskAnalyzer $analyzer): RedirectResponse
    {
        $this->authorizeTask($task);
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
        TaskEvent::create(['task_id' => $task->id, 'user_id' => auth()->id(), 'event' => 'reanalyzed', 'payload' => ['confidence' => $result->confidence]]);
        return back();
    }

    public function pushToMonday(Task $task, Request $request): RedirectResponse
    {
        $this->authorizeTask($task);
        $request->validate(['source_account_id' => 'required|exists:source_accounts,id']);
        PushTaskToProviderJob::dispatch($task->id, (int) $request->source_account_id, 'monday');
        return back();
    }

    public function pushToWrike(Task $task, Request $request): RedirectResponse
    {
        $this->authorizeTask($task);
        $request->validate(['source_account_id' => 'required|exists:source_accounts,id']);
        PushTaskToProviderJob::dispatch($task->id, (int) $request->source_account_id, 'wrike');
        return back();
    }

    public function addComment(Task $task, Request $request): RedirectResponse
    {
        $this->authorizeTask($task);
        $data = $request->validate(['body' => 'required|string|max:5000']);
        TaskComment::create(['task_id' => $task->id, 'user_id' => $request->user()->id, 'body' => $data['body']]);
        return back();
    }

    protected function authorizeTask(Task $task): void
    {
        abort_if($task->user_id !== auth()->id(), 403);
    }

    protected function validateData(Request $request, bool $partial = false): array
    {
        $rules = [
            'title' => ($partial ? 'sometimes|' : '') . 'required|string|max:500',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:' . implode(',', Task::STATUSES),
            'priority' => 'sometimes|in:' . implode(',', Task::PRIORITIES),
            'due_date' => 'nullable|date',
        ];
        return $request->validate($rules);
    }
}
