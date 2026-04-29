<?php

namespace App\Jobs;

use App\Models\ExternalTaskMapping;
use App\Models\SourceAccount;
use App\Models\Task;
use App\Models\TaskEvent;
use App\Services\Sources\SourceProviderRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushTaskToProviderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $taskId, public int $sourceAccountId, public string $provider) {}

    public function handle(SourceProviderRegistry $registry): void
    {
        $task = Task::find($this->taskId);
        $account = SourceAccount::find($this->sourceAccountId);
        if (! $task || ! $account || $account->type !== $this->provider) return;

        $existing = ExternalTaskMapping::where('task_id', $task->id)
            ->where('provider', $this->provider)
            ->first();

        if ($existing) {
            $ok = $registry->for($account)->updateExternalTask($existing, $task);
            $task->update([
                'external_sync_status' => $ok ? 'synced' : 'sync_failed',
                'external_last_synced_at' => now(),
            ]);
        } else {
            $mapping = $registry->for($account)->pushTask($account, $task);
            $task->update([
                'external_sync_status' => $mapping ? 'synced' : 'sync_failed',
                'external_last_synced_at' => now(),
            ]);
        }

        TaskEvent::create([
            'task_id' => $task->id,
            'event' => 'pushed',
            'payload' => ['provider' => $this->provider, 'account_id' => $account->id],
        ]);
    }
}
