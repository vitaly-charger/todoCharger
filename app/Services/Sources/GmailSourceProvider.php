<?php

namespace App\Services\Sources;

use App\Jobs\AnalyzeSourceMessageJob;
use App\Models\SourceAccount;
use App\Models\SyncLog;
use Illuminate\Support\Collection;

/**
 * Gmail provider (skeleton).
 *
 * Wire up real Gmail API calls using the user's OAuth tokens stored in
 * $account->credentials (refresh_token, access_token, expires_at). Use
 * the Gmail REST API: users.messages.list with q=is:unread, then
 * users.messages.get for each id. Map to SourceMessage rows below.
 */
class GmailSourceProvider extends AbstractSourceProvider
{
    public function type(): string { return SourceAccount::TYPE_GMAIL; }

    public function sync(SourceAccount $account): SyncLog
    {
        $log = $this->startLog($account);
        try {
            $items = $this->fetchNewMessages($account); // collection of arrays
            $messages = $this->storeMessages($account, $items);
            $messages->each(fn ($m) => AnalyzeSourceMessageJob::dispatch($m->id));
            $account->update([
                'last_synced_at' => now(),
                'last_sync_status' => 'success',
            ]);
            return $this->finishLog($log, $messages->count(), 0);
        } catch (\Throwable $e) {
            $account->update(['last_sync_status' => 'failed']);
            return $this->finishLog($log, 0, 0, $e->getMessage());
        }
    }

    /**
     * TODO: Implement using google/apiclient or raw Http calls.
     * Each item must include keys:
     *   external_id, external_parent_id (thread id), sender_name, sender_identifier,
     *   title (subject), body_text, body_html, source_url (gmail thread URL),
     *   received_at, metadata.
     */
    protected function fetchNewMessages(SourceAccount $account): Collection
    {
        return collect();
    }
}
