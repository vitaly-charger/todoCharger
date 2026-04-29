<?php

namespace App\Services;

use danog\MadelineProto\API;
use danog\MadelineProto\Logger as MtLogger;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use danog\MadelineProto\Settings\Logger as LoggerSettings;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around MadelineProto for the multi-step user login flow.
 *
 *   start($phone)      → sends SMS code, returns ['phone_code_hash' => ...]
 *   submitCode($code)  → either logs in or signals 2FA required
 *   submitPassword($p) → completes 2FA login
 *   me()               → fetches the now-logged-in user info
 *
 * One instance == one SourceAccount, identified by $sessionPath.
 */
class TelegramUserAuth
{
    public function __construct(public readonly string $sessionPath)
    {
        // Make sure parent dir exists.
        @mkdir(dirname($this->sessionPath), 0775, true);
    }

    public static function forAccountId(int $accountId): self
    {
        return new self(storage_path("app/telegram-sessions/{$accountId}.madeline"));
    }

    protected function api(): API
    {
        $apiId = (int) config('services.telegram.api_id');
        $apiHash = (string) config('services.telegram.api_hash');

        if ($apiId <= 0 || $apiHash === '') {
            throw new \RuntimeException(
                'Telegram API credentials missing. Set TELEGRAM_API_ID and TELEGRAM_API_HASH in .env (get them from https://my.telegram.org).'
            );
        }

        $settings = (new Settings())
            ->setAppInfo(
                (new AppInfo())
                    ->setApiId($apiId)
                    ->setApiHash($apiHash)
            );

        // Quiet logs (MadelineProto is very chatty by default).
        $settings->setLogger(
            (new LoggerSettings())
                ->setType(MtLogger::FILE_LOGGER)
                ->setExtra(storage_path('logs/madeline.log'))
                ->setLevel(MtLogger::LEVEL_WARNING)
        );

        return new API($this->sessionPath, $settings);
    }

    /**
     * @return array{phone_code_hash:string}
     */
    public function start(string $phone): array
    {
        $api = $this->api();
        $res = $api->phoneLogin($phone);
        return ['phone_code_hash' => (string) ($res['phone_code_hash'] ?? '')];
    }

    /**
     * @return array{status:'ok'|'needs_password'}
     */
    public function submitCode(string $code): array
    {
        $api = $this->api();
        try {
            $res = $api->completePhoneLogin($code);
        } catch (\Throwable $e) {
            // MadelineProto throws RPCErrorException with SESSION_PASSWORD_NEEDED
            if (str_contains((string) $e->getMessage(), 'SESSION_PASSWORD_NEEDED')) {
                return ['status' => 'needs_password'];
            }
            throw $e;
        }

        $type = $res['_'] ?? '';
        if ($type === 'account.password') {
            return ['status' => 'needs_password'];
        }
        return ['status' => 'ok'];
    }

    public function submitPassword(string $password): void
    {
        $api = $this->api();
        $api->complete2faLogin($password);
    }

    /**
     * @return array{id:int|string,username:?string,first_name:?string,last_name:?string,phone:?string}
     */
    public function me(): array
    {
        $api = $this->api();
        $self = $api->getSelf();
        return [
            'id'         => $self['id'] ?? '',
            'username'   => $self['username'] ?? null,
            'first_name' => $self['first_name'] ?? null,
            'last_name'  => $self['last_name'] ?? null,
            'phone'      => $self['phone'] ?? null,
        ];
    }

    public function logoutAndDelete(): void
    {
        try {
            $api = $this->api();
            $api->logout();
        } catch (\Throwable $e) {
            Log::warning('Telegram logout failed', ['err' => $e->getMessage()]);
        }
        // Wipe session files.
        foreach (glob($this->sessionPath . '*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->sessionPath);
    }
}
