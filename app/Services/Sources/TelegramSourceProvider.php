<?php

namespace App\Services\Sources;

use App\Jobs\AnalyzeSourceMessageJob;
use App\Models\SourceAccount;
use App\Models\SourceMessage;
use App\Models\SyncLog;
use Illuminate\Support\Collection;

/**
 * Telegram provider (skeleton).
 *
 * Webhook-first: TelegramWebhookController calls handleUpdate(). The fallback
 * sync() uses getUpdates if a long-polling worker is desired.
 *
 * Credentials JSON expected: { "bot_token": "..." }
 * Settings JSON expected: { "allowed_chat_ids": [123, -100123], "allowed_chat_usernames": ["@me"] }
 */
class TelegramSourceProvider extends AbstractSourceProvider
{
    public function type(): string { return SourceAccount::TYPE_TELEGRAM; }

    public function sync(SourceAccount $account): SyncLog
    {
        $log = $this->startLog($account);
        try {
            $items = $this->fetchUpdates($account);
            $messages = $this->storeMessages($account, $items);
            $messages->each(fn ($m) => AnalyzeSourceMessageJob::dispatch($m->id));
            $account->update(['last_synced_at' => now(), 'last_sync_status' => 'success']);
            return $this->finishLog($log, $messages->count(), 0);
        } catch (\Throwable $e) {
            $account->update(['last_sync_status' => 'failed']);
            return $this->finishLog($log, 0, 0, $e->getMessage());
        }
    }

    public function handleUpdate(SourceAccount $account, array $update): ?SourceMessage
    {
        $msg = $update['message'] ?? $update['edited_message'] ?? $update['channel_post'] ?? null;
        if (! $msg || empty($msg['text'])) return null;

        $allowed = (array) ($account->settings['allowed_chat_ids'] ?? []);
        if ($allowed && ! in_array((int) ($msg['chat']['id'] ?? 0), $allowed, true)) {
            return null;
        }

        $message = SourceMessage::updateOrCreate(
            [
                'source_account_id' => $account->id,
                'external_id' => ($msg['chat']['id'] ?? 0) . ':' . ($msg['message_id'] ?? 0),
            ],
            [
                'source_type' => SourceAccount::TYPE_TELEGRAM,
                'sender_name' => trim(($msg['from']['first_name'] ?? '') . ' ' . ($msg['from']['last_name'] ?? '')),
                'sender_identifier' => $msg['from']['username'] ?? (string) ($msg['from']['id'] ?? ''),
                'title' => $msg['chat']['title'] ?? $msg['chat']['username'] ?? null,
                'body_text' => $msg['text'],
                'metadata' => $msg,
                'received_at' => isset($msg['date']) ? now()->setTimestamp((int) $msg['date']) : now(),
            ],
        );

        AnalyzeSourceMessageJob::dispatch($message->id);
        return $message;
    }

    protected function fetchUpdates(SourceAccount $account): Collection
    {
        // TODO: implement long-poll fallback using getUpdates if needed.
        return collect();
    }
}
