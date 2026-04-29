<?php

namespace App\Http\Controllers;

use App\Models\SourceAccount;
use App\Services\Sources\TelegramSourceProvider;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, string $token, TelegramSourceProvider $provider)
    {
        // Token in URL = simple shared secret (account.settings.webhook_token).
        $account = SourceAccount::where('type', SourceAccount::TYPE_TELEGRAM)
            ->where('enabled', true)
            ->get()
            ->first(fn ($a) => hash_equals((string) ($a->settings['webhook_token'] ?? ''), $token));

        abort_unless($account, 404);

        $provider->handleUpdate($account, $request->all());
        return response()->json(['ok' => true]);
    }
}
