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
 * monday.com provider (skeleton).
 *
 * Settings JSON expected:
 * {
 *   "workspace_id": "...",
 *   "board_id": "...",
 *   "group_id": null,
 *   "status_column_id": null,
 *   "priority_column_id": null,
 *   "due_date_column_id": null,
 *   "owner_column_id": null,
 *   "import_items": true,
 *   "import_updates": true,
 *   "create_tasks_from_updates": true,
 *   "allow_push_to_monday": true,
 *   "status_mapping": { "todo":"To Do","in_progress":"Working on it","waiting":"Waiting","done":"Done" },
 *   "priority_mapping": { "low":"Low","normal":"Medium","high":"High","urgent":"Critical" }
 * }
 *
 * Credentials JSON expected: { "api_token": "..." }
 */
class MondaySourceProvider extends AbstractSourceProvider
{
    public function type(): string { return SourceAccount::TYPE_MONDAY; }

    public function sync(SourceAccount $account): SyncLog
    {
        $log = $this->startLog($account);
        try {
            $imported = collect();

            if ($account->settings['import_items'] ?? true) {
                $imported = $imported->merge($this->fetchItems($account));
            }
            if ($account->settings['import_updates'] ?? true) {
                $imported = $imported->merge($this->fetchUpdates($account));
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
        if (! ($account->settings['allow_push_to_monday'] ?? false)) return null;
        // TODO: GraphQL mutation `create_item` on board_id/group_id, set columns from mappings.
        // Example: mutation { create_item (board_id: ..., item_name: "...", column_values: "...") { id } }
        return null;
    }

    public function updateExternalTask(ExternalTaskMapping $mapping, Task $task): bool
    {
        // TODO: GraphQL mutation `change_simple_column_value` for status/priority/due.
        return false;
    }

    protected function fetchItems(SourceAccount $account): Collection
    {
        // TODO: GraphQL query `boards (ids:[...]) { items_page { items { ... } } }`.
        // Build entries: external_id="item:{id}", source_url, sender_name=creator,
        // title=item.name, body_text=column summary, metadata=raw item.
        return collect();
    }

    protected function fetchUpdates(SourceAccount $account): Collection
    {
        // TODO: GraphQL query for updates within the board, external_id="update:{id}".
        return collect();
    }

    protected function http(SourceAccount $account)
    {
        $token = $account->credentials['api_token'] ?? config('inbox.providers.monday.api_token');
        return Http::withToken($token)
            ->withHeaders(['API-Version' => '2024-01'])
            ->baseUrl('https://api.monday.com/v2');
    }
}
