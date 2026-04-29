<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskCommentResource;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskCommentController extends Controller
{
    public function index(Request $request, Task $task): AnonymousResourceCollection
    {
        $this->authorizeTask($request, $task);
        return TaskCommentResource::collection($task->comments()->with('user')->get());
    }

    public function store(Request $request, Task $task): TaskCommentResource
    {
        $this->authorizeTask($request, $task);
        $data = $request->validate(['body' => 'required|string|max:5000']);
        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        return new TaskCommentResource($comment->load('user'));
    }

    public function update(Request $request, Task $task, TaskComment $comment): TaskCommentResource
    {
        $this->authorizeTask($request, $task);
        abort_if($comment->task_id !== $task->id, 404);
        abort_if($comment->user_id !== $request->user()->id, 403);
        $data = $request->validate(['body' => 'required|string|max:5000']);
        $comment->update($data);
        return new TaskCommentResource($comment->load('user'));
    }

    public function destroy(Request $request, Task $task, TaskComment $comment): JsonResponse
    {
        $this->authorizeTask($request, $task);
        abort_if($comment->task_id !== $task->id, 404);
        abort_if($comment->user_id !== $request->user()->id, 403);
        $comment->delete();
        return response()->json(['ok' => true]);
    }

    protected function authorizeTask(Request $request, Task $task): void
    {
        abort_if($task->user_id !== $request->user()->id, 403);
    }
}
