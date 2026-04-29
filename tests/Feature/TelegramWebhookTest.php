<?php

namespace Tests\Feature;

use App\Models\SourceAccount;
use App\Models\SourceMessage;
use App\Models\User;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    public function test_webhook_with_invalid_token_is_rejected(): void
    {
        $this->postJson('/webhooks/telegram/bogus', [])->assertNotFound();
    }

    public function test_valid_webhook_creates_source_message(): void
    {
        $user = User::factory()->create();
        $account = SourceAccount::create([
            'user_id' => $user->id,
            'type' => 'telegram',
            'name' => 'My bot',
            'enabled' => true,
            'settings' => ['webhook_token' => 'sek-r3t', 'allowed_chat_ids' => [42]],
            'credentials' => ['bot_token' => 'fake'],
        ]);

        $payload = [
            'update_id' => 123,
            'message' => [
                'message_id' => 7,
                'from' => ['id' => 1, 'first_name' => 'Pk'],
                'chat' => ['id' => 42, 'type' => 'private'],
                'date' => time(),
                'text' => 'Please remind me to file taxes',
            ],
        ];

        $this->postJson('/webhooks/telegram/sek-r3t', $payload)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('source_messages', [
            'source_account_id' => $account->id,
            'external_id' => '42:7',
        ]);
    }
}
