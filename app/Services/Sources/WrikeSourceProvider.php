<?php

namespace App\Services\Sources;

use App\Jobs\AnalyzeSourceMessageJob;
use App\Models\ExternalTaskMapping;
use App\Models\SourceAccount;
use App\Models\SyncLog;
use App\Models\Task;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * Wrike provider (skeleton).
 *
 * Settings JSON expected:
 * {
 *   "account_id": "...",
 *   "space_id": null,
 *   "folder_id": null,
 *   "project_id": null,
 *   "import_tasks": true,
 *   "import_comments": true,
 *   "create_tasks_from_comments": true,
 *   "allow_push_to_wrike": true,
 *   "status_mapping": { "todo":"Active","in_progress":"In Progress","waiting":"Deferred","done":"Completed" },
 *   "priority_mapping": { "low":"Low","normal":"Normal","high":"High","urgent":"High" }
 * }
 *
 * Credentials JSON expected: { "api_token": "...", "refresh_token": "..." }
 */
class WrikeSourceProvider extends AbstractSourceProvider
{
    public function type(): string { return SourceAccount::TYPE_WRIKE; }

    public function sync(SourceAccount $account): SyncLog
    {
        $log = $this->startLog($account);
        try {
            $imported = collect();
            if ($account->settings['import_tasks'] ?? true) {
                $imported = $imported->merge($this->fetchTasks($account));
            }
            if ($account->settings['import_comments'] ?? true) {
                $imported = $imported->merge($this->fetchComments($account));
            }
            $messages = $this->storeMessages($account, $imported);
            $messages->each(fn ($m) => AnalyzeSourceMessageJob::dispatch($m->id));
            $account->update(['last_synced_at' => now(), 'last_sync_status' => 'success']);
            return $this->finishLog($log, $messages->count(), 0);
        } catch (\Throwable $e) {
            $account->update(['last_sync_status' => 'failed']);
            return $this->finishLog($log, 0, 0, $e->getMessage());
        }
    }

    public function pushTask(SourceAccount $account, Task $task): ?ExternalTaskMapping
    {
        if (! ($account->settings['allow_push_to_wrike'] ?? false)) return null;
        // TODO: POST /folders/{folderId}/tasks with title, description, importance, dates.
        return null;
    }

    public function updateExternalTask(ExternalTaskMapping $mapping, Task $task): bool
    {
        // TODO: PUT /tasks/{id} with status/importance/dates from mapping.
        return false;
    }

    protected function fetchTasks(SourceAccount $account): Collection
    {
        // TODO: GET /folders/{id}/tasks?fields=[...]
        return collect();
    }

    protected function fetchComments(SourceAccount $account): Collection
    {
        // TODO: GET /comments?folderId=... or per-task GET /tasks/{id}/comments
        return collect();
    }

    protected function http(SourceAccount $account)
    {
        $token = $account->credentials['api_token'] ?? config('inbox.providers.wrike.api_token');
        return Http::withToken($token)->baseUrl('https://www.wrike.com/api/v4');
    }
}
