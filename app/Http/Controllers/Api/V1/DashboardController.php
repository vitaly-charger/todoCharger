<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\SyncLog;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $tasks = Task::where('user_id', $userId);

        return response()->json([
            'open_tasks' => (clone $tasks)->whereNotIn('status', ['done', 'ignored'])->count(),
            'urgent_tasks' => (clone $tasks)->where('priority', 'urgent')->whereNotIn('status', ['done', 'ignored'])->count(),
            'waiting_tasks' => (clone $tasks)->where('status', 'waiting')->count(),
            'tasks_by_source' => (clone $tasks)
                ->whereNotIn('status', ['done', 'ignored'])
                ->selectRaw('source_type, COUNT(*) as count')
                ->groupBy('source_type')->get(),
            'recent_tasks' => TaskResource::collection(
                (clone $tasks)->latest()->limit(10)->get()
            ),
            'failed_syncs' => SyncLog::where('status', 'failed')->latest()->limit(5)->get(),
        ]);
    }
}
