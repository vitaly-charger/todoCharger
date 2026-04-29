<?php

namespace App\Jobs;

use App\Models\SourceAccount;
use App\Services\Sources\SourceProviderRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSourceAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $sourceAccountId) {}

    public function handle(SourceProviderRegistry $registry): void
    {
        $account = SourceAccount::find($this->sourceAccountId);
        if (! $account || ! $account->enabled) return;
        $registry->for($account)->sync($account);
    }
}
