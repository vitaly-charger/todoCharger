<?php

namespace App\Services\Sources;

use App\Models\SourceAccount;
use App\Models\SourceMessage;
use App\Models\SyncLog;
use Illuminate\Support\Collection;

abstract class AbstractSourceProvider
{
    abstract public function type(): string;

    /**
     * Sync this account: pull new external items, store SourceMessage rows,
     * and queue AI analysis. Return the SyncLog row.
     */
    abstract public function sync(SourceAccount $account): SyncLog;

    /**
     * Optionally push a local task to this provider (monday/wrike).
     */
    public function pushTask(SourceAccount $account, \App\Models\Task $task): ?\App\Models\ExternalTaskMapping
    {
        return null;
    }

    /**
     * Optionally update a previously pushed task (status/due/priority).
     */
    public function updateExternalTask(\App\Models\ExternalTaskMapping $mapping, \App\Models\Task $task): bool
    {
        return false;
    }

    protected function startLog(SourceAccount $account): SyncLog
    {
        return SyncLog::create([
            'source_account_id' => $account->id,
            'source_type' => $account->type,
            'status' => 'success',
            'started_at' => now(),
        ]);
    }

    protected function finishLog(SyncLog $log, int $imported, int $createdTasks, ?string $error = null): SyncLog
    {
        $log->fill([
            'finished_at' => now(),
            'imported_count' => $imported,
            'created_task_count' => $createdTasks,
            'status' => $error ? 'failed' : 'success',
            'error_message' => $error,
        ])->save();
        return $log;
    }

    protected function storeMessages(SourceAccount $account, Collection $items): Collection
    {
        return $items->map(function (array $row) use ($account) {
            return SourceMessage::updateOrCreate(
                [
                    'source_account_id' => $account->id,
                    'external_id' => $row['external_id'],
                ],
                array_merge($row, [
                    'source_type' => $account->type,
                ]),
            );
        });
    }
}
