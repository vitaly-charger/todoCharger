<?php

namespace App\Services\Sources;

use App\Jobs\AnalyzeSourceMessageJob;
use App\Models\SourceAccount;
use App\Models\SyncLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * Slack provider (skeleton).
 *
 * Settings JSON expected:
 *   { "channels": ["C0123","C0456"], "include_dms": true }
 *
 * Credentials JSON expected:
 *   { "bot_token": "xoxb-...", "user_token": "xoxp-..." }
 */
class SlackSourceProvider extends AbstractSourceProvider
{
    public function type(): string { return SourceAccount::TYPE_SLACK; }

    public function sync(SourceAccount $account): SyncLog
    {
        $log = $this->startLog($account);
        try {
            $items = $this->fetchMessages($account);
            $messages = $this->storeMessages($account, $items);
            $messages->each(fn ($m) => AnalyzeSourceMessageJob::dispatch($m->id));
            $account->update(['last_synced_at' => now(), 'last_sync_status' => 'success']);
            return $this->finishLog($log, $messages->count(), 0);
        } catch (\Throwable $e) {
            $account->update(['last_sync_status' => 'failed']);
            return $this->finishLog($log, 0, 0, $e->getMessage());
        }
    }

    protected function fetchMessages(SourceAccount $account): Collection
    {
        // TODO: call conversations.history per channel using bot token.
        // Build entries: external_id = "{channel}:{ts}", source_url = permalink via chat.getPermalink.
        return collect();
    }

    protected function http(SourceAccount $account)
    {
        $token = $account->credentials['bot_token'] ?? null;
        return Http::withToken($token)->baseUrl('https://slack.com/api/');
    }
}
