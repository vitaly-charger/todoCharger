<?php

namespace App\Http\Controllers;

use App\Models\AiAnalysisLog;
use App\Models\SourceAccount;
use App\Models\SyncLog;
use App\Models\Task;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $userId = auth()->id();

        $tasks = Task::where('user_id', $userId);

        return Inertia::render('Dashboard', [
            'stats' => [
                'open_tasks' => (clone $tasks)->whereNotIn('status', ['done', 'ignored'])->count(),
                'urgent' => (clone $tasks)->where('priority', 'urgent')->whereNotIn('status', ['done', 'ignored'])->count(),
                'needs_review' => (clone $tasks)->where('needs_review', true)->count(),
                'closed_this_week' => (clone $tasks)->where('completed_at', '>=', now()->startOfWeek())->count(),
            ],
            'recent_ai_tasks' => (clone $tasks)
                ->whereNotNull('ai_summary')
                ->latest()->limit(5)->get(),
            'needs_review' => (clone $tasks)->where('needs_review', true)->latest()->limit(3)->get(),
            'today' => (clone $tasks)
                ->whereDate('due_date', today())
                ->whereNotIn('status', ['done', 'ignored'])
                ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 WHEN 'low' THEN 4 END")
                ->limit(5)->get(),
            'by_source' => (clone $tasks)
                ->whereNotIn('status', ['done', 'ignored'])
                ->selectRaw('source_type, COUNT(*) as count')
                ->groupBy('source_type')->get(),
            'failed_syncs' => SyncLog::where('status', 'failed')
                ->latest()->limit(3)->get(),
            'sources_count' => SourceAccount::where('user_id', $userId)->count(),
            'ai_logs_count' => AiAnalysisLog::count(),
        ]);
    }
}
