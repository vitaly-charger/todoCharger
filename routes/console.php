<?php

use App\Jobs\SyncSourceAccountJob;
use App\Models\SourceAccount;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inbox:sync {type?}', function (?string $type = null) {
    $q = SourceAccount::query()->where('enabled', true);
    if ($type) $q->where('type', $type);
    foreach ($q->get() as $acct) {
        SyncSourceAccountJob::dispatch($acct->id);
        $this->info("Queued sync for [{$acct->type}] {$acct->name}");
    }
})->purpose('Queue sync for all enabled source accounts (optionally by type)');

$dispatchAll = function (string $type) {
    SourceAccount::where('enabled', true)->where('type', $type)->get()
        ->each(fn ($a) => SyncSourceAccountJob::dispatch($a->id));
};

Schedule::call(fn () => $dispatchAll(SourceAccount::TYPE_GMAIL))
    ->cron('*/' . max(1, (int) config('inbox.sync.gmail_minutes', 5)) . ' * * * *')
    ->name('sync-gmail')->withoutOverlapping();

Schedule::call(fn () => $dispatchAll(SourceAccount::TYPE_SLACK))
    ->cron('*/' . max(1, (int) config('inbox.sync.slack_minutes', 5)) . ' * * * *')
    ->name('sync-slack')->withoutOverlapping();

Schedule::call(fn () => $dispatchAll(SourceAccount::TYPE_MONDAY))
    ->cron('*/' . max(1, (int) config('inbox.sync.monday_minutes', 5)) . ' * * * *')
    ->name('sync-monday')->withoutOverlapping();

Schedule::call(fn () => $dispatchAll(SourceAccount::TYPE_WRIKE))
    ->cron('*/' . max(1, (int) config('inbox.sync.wrike_minutes', 5)) . ' * * * *')
    ->name('sync-wrike')->withoutOverlapping();

// Telegram is webhook-first; this is a fallback poll.
Schedule::call(fn () => $dispatchAll(SourceAccount::TYPE_TELEGRAM))
    ->cron('*/' . max(1, (int) config('inbox.sync.telegram_fallback_minutes', 5)) . ' * * * *')
    ->name('sync-telegram')->withoutOverlapping();

Schedule::call(function () {
    \App\Models\AiAnalysisLog::where('created_at', '<', now()->subDays(90))->delete();
    \App\Models\SyncLog::where('created_at', '<', now()->subDays(90))->delete();
})->dailyAt('03:00')->name('cleanup-logs');
