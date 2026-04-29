<?php

namespace App\Http\Middleware;

use App\Models\SourceAccount;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'sidebar' => fn () => $this->sidebar($request),
        ];
    }

    /**
     * Counts powering the sidebar. Lazy: only resolves when Inertia requests it.
     */
    protected function sidebar(Request $request): ?array
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        $base = Task::where('user_id', $user->id);

        $statusRows = (clone $base)->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status')->all();
        $statuses = array_fill_keys(['inbox','todo','in_progress','waiting','done','ignored'], 0);
        foreach ($statusRows as $k => $v) { $statuses[$k] = (int) $v; }

        $sourceRows = (clone $base)
            ->whereNotIn('status', ['done','ignored'])
            ->selectRaw('source_type, COUNT(*) as c')
            ->groupBy('source_type')->pluck('c', 'source_type')->all();
        $sources = array_fill_keys(['gmail','slack','telegram','manual','monday','wrike'], 0);
        foreach ($sourceRows as $k => $v) { $sources[$k] = (int) $v; }

        $smart = [
            'review' => (clone $base)->where('needs_review', true)->count(),
            'today'  => (clone $base)->whereDate('due_date', today())->whereNotIn('status', ['done','ignored'])->count(),
            'urgent' => (clone $base)->where('priority', 'urgent')->whereNotIn('status', ['done','ignored'])->count(),
        ];

        $latestSync = SourceAccount::where('user_id', $user->id)
            ->whereNotNull('last_synced_at')
            ->orderByDesc('last_synced_at')->value('last_synced_at');

        return [
            'statusCounts' => $statuses,
            'sourceCounts' => $sources,
            'smartCounts'  => $smart,
            'lastSyncAt'   => optional($latestSync)->diffForHumans(),
        ];
    }
}
