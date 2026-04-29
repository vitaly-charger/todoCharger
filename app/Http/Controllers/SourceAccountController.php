<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSourceAccountJob;
use App\Models\SourceAccount;
use App\Services\TelegramUserAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

class SourceAccountController extends Controller
{
    public function index(): Response
    {
        $accounts = SourceAccount::where('user_id', auth()->id())
            ->withCount(['messages', 'tasks'])
            ->latest()->get()
            ->map(function (SourceAccount $a) {
                $arr = $a->toArray();
                $arr['connected'] = $this->isConnected($a);
                $arr['mtproto_state'] = $a->credentials['mtproto_state'] ?? null;
                return $arr;
            });

        return Inertia::render('Sources/Index', [
            'accounts' => $accounts,
            'types' => SourceAccount::TYPES,
            'telegram_user_supported' => (bool) (config('services.telegram.api_id') && config('services.telegram.api_hash')),
        ]);
    }

    private function isConnected(SourceAccount $a): bool
    {
        $c = $a->credentials ?? [];
        if (!is_array($c)) return false;
        if ($a->type === SourceAccount::TYPE_GMAIL) {
            return !empty($c['access_token']) || !empty($c['refresh_token']);
        }
        if ($a->type === SourceAccount::TYPE_TELEGRAM) {
            // Bot token OR a fully-completed user-account login.
            if (!empty($c['token'])) return true;
            return ($c['mtproto'] ?? false) === true && ($c['mtproto_state'] ?? null) === 'connected';
        }
        return !empty($c['token']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:' . implode(',', SourceAccount::TYPES),
            'name' => 'required|string|max:200',
            'identifier' => 'nullable|string|max:200',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
            'enabled' => 'sometimes|boolean',
        ]);
        $data['user_id'] = auth()->id();
        $account = SourceAccount::create($data);
        return redirect()->route('sources.show', $account);
    }

    public function show(SourceAccount $source): Response
    {
        $this->authorizeAccount($source);
        $source->loadCount(['messages', 'tasks']);
        return Inertia::render('Sources/Show', [
            'account' => $source,
            'recent_logs' => $source->syncLogs()->latest()->limit(20)->get(),
        ]);
    }

    public function update(Request $request, SourceAccount $source): RedirectResponse
    {
        $this->authorizeAccount($source);
        $data = $request->validate([
            'name' => 'sometimes|string|max:200',
            'identifier' => 'nullable|string|max:200',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
            'enabled' => 'sometimes|boolean',
        ]);
        $source->update($data);
        return back();
    }

    public function destroy(SourceAccount $source): RedirectResponse
    {
        $this->authorizeAccount($source);
        $source->delete();
        return redirect()->route('sources.index');
    }

    public function sync(SourceAccount $source): RedirectResponse
    {
        $this->authorizeAccount($source);
        SyncSourceAccountJob::dispatch($source->id);
        return back()->with('status', 'Sync queued.');
    }

    public function toggle(SourceAccount $source): RedirectResponse
    {
        $this->authorizeAccount($source);
        $source->update(['enabled' => ! $source->enabled]);
        return back();
    }

    protected function authorizeAccount(SourceAccount $account): void
    {
        abort_if($account->user_id !== auth()->id(), 403);
    }

    /**
     * Start Gmail OAuth (asks the user to grant gmail.readonly).
     */
    public function connectGmail(): SymfonyRedirect
    {
        return Socialite::driver('google')
            ->redirectUrl(url('/sources/gmail/callback'))
            ->scopes([
                'openid', 'email', 'profile',
                'https://www.googleapis.com/auth/gmail.readonly',
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    /**
     * Callback that stores the OAuth credentials on a SourceAccount.
     */
    public function connectGmailCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(url('/sources/gmail/callback'))
                ->user();
        } catch (\Throwable $e) {
            return redirect()->route('sources.index')->withErrors([
                'source' => 'Gmail connect failed: ' . $e->getMessage(),
            ]);
        }

        $email = (string) $googleUser->getEmail();
        $expiresIn = (int) ($googleUser->expiresIn ?? 0);

        SourceAccount::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'type' => SourceAccount::TYPE_GMAIL,
                'identifier' => $email,
            ],
            [
                'name' => 'Gmail – ' . $email,
                'credentials' => [
                    'access_token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                    'expires_in' => $expiresIn,
                    'expires_at' => $expiresIn > 0
                        ? now()->addSeconds($expiresIn)->toIso8601String()
                        : null,
                    'scopes' => [
                        'openid', 'email', 'profile',
                        'https://www.googleapis.com/auth/gmail.readonly',
                    ],
                ],
                'enabled' => true,
            ],
        );

        return redirect()->route('sources.index')->with('status', 'Gmail connected as ' . $email);
    }

    /**
     * Generic token-based connect for Slack / Telegram / monday.com / Wrike.
     * Validates the token against the provider's whoami endpoint, then upserts
     * a SourceAccount with the credentials encrypted in the JSON column.
     */
    public function connectToken(Request $request, string $type): RedirectResponse
    {
        $allowed = [
            SourceAccount::TYPE_SLACK,
            SourceAccount::TYPE_TELEGRAM,
            SourceAccount::TYPE_MONDAY,
            SourceAccount::TYPE_WRIKE,
        ];
        abort_unless(in_array($type, $allowed, true), 404);

        $request->validate(['token' => 'required|string|min:10|max:512']);
        $token = trim((string) $request->input('token'));

        try {
            [$identifier, $name, $extra] = match ($type) {
                SourceAccount::TYPE_SLACK    => $this->probeSlack($token),
                SourceAccount::TYPE_TELEGRAM => $this->probeTelegram($token),
                SourceAccount::TYPE_MONDAY   => $this->probeMonday($token),
                SourceAccount::TYPE_WRIKE    => $this->probeWrike($token),
            };
        } catch (\Throwable $e) {
            return back()->withErrors(['source' => ucfirst($type) . ' connect failed: ' . $e->getMessage()]);
        }

        SourceAccount::updateOrCreate(
            [
                'user_id'    => auth()->id(),
                'type'       => $type,
                'identifier' => $identifier,
            ],
            [
                'name'        => $name,
                'credentials' => array_merge(['token' => $token], $extra),
                'enabled'     => true,
            ],
        );

        return redirect()->route('sources.index')->with('status', ucfirst($type) . ' connected: ' . $identifier);
    }

    /** @return array{0:string,1:string,2:array} */
    protected function probeSlack(string $token): array
    {
        $res = Http::asForm()->post('https://slack.com/api/auth.test', ['token' => $token])->json();
        if (! ($res['ok'] ?? false)) {
            throw new \RuntimeException($res['error'] ?? 'invalid token');
        }
        $team = (string) ($res['team'] ?? 'workspace');
        $user = (string) ($res['user'] ?? 'bot');
        $id   = (string) ($res['team_id'] ?? $team);
        return [$id, "Slack \u{2013} {$team} (@{$user})", [
            'team'    => $team,
            'team_id' => $id,
            'user'    => $user,
            'user_id' => $res['user_id'] ?? null,
        ]];
    }

    /** @return array{0:string,1:string,2:array} */
    protected function probeTelegram(string $token): array
    {
        $res = Http::get("https://api.telegram.org/bot{$token}/getMe")->json();
        if (! ($res['ok'] ?? false)) {
            throw new \RuntimeException($res['description'] ?? 'invalid bot token');
        }
        $bot = $res['result'] ?? [];
        $username = (string) ($bot['username'] ?? 'bot');
        $id = (string) ($bot['id'] ?? $username);
        return ['@' . $username, "Telegram \u{2013} @{$username}", [
            'bot_id'    => $id,
            'username'  => $username,
            'first_name'=> $bot['first_name'] ?? null,
        ]];
    }

    /** @return array{0:string,1:string,2:array} */
    protected function probeMonday(string $token): array
    {
        $res = Http::withHeaders([
            'Authorization' => $token,
            'API-Version'   => '2024-01',
        ])->post('https://api.monday.com/v2', [
            'query' => '{ me { id name email } }',
        ])->json();

        $me = $res['data']['me'] ?? null;
        if (! $me) {
            throw new \RuntimeException($res['errors'][0]['message'] ?? 'invalid API token');
        }
        $email = (string) ($me['email'] ?? $me['id']);
        return [$email, "monday.com \u{2013} " . ($me['name'] ?? $email), [
            'user_id' => $me['id'] ?? null,
            'email'   => $email,
        ]];
    }

    /** @return array{0:string,1:string,2:array} */
    protected function probeWrike(string $token): array
    {
        $res = Http::withToken($token)
            ->get('https://www.wrike.com/api/v4/contacts', ['me' => 'true'])
            ->json();

        $me = $res['data'][0] ?? null;
        if (! $me) {
            throw new \RuntimeException($res['errorDescription'] ?? 'invalid permanent token');
        }
        $email = (string) ($me['profiles'][0]['email'] ?? $me['id']);
        $name  = trim(($me['firstName'] ?? '') . ' ' . ($me['lastName'] ?? '')) ?: $email;
        return [$email, "Wrike \u{2013} {$name}", [
            'user_id' => $me['id'] ?? null,
            'email'   => $email,
        ]];
    }

    /* ------------------------------------------------------------------
     * Telegram user-account (MTProto) connect — multi-step flow.
     *  POST /sources/telegram/user/start    { phone }       → SMS code sent
     *  POST /sources/telegram/user/code     { code }        → ok or needs_password
     *  POST /sources/telegram/user/password { password }    → done
     * ------------------------------------------------------------------ */

    public function telegramUserStart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => 'required|string|min:5|max:32',
        ]);
        $phone = preg_replace('/[^\d+]/', '', $data['phone']) ?? '';
        if ($phone === '') {
            return back()->withErrors(['source' => 'Invalid phone number.']);
        }

        // Reuse any pending account for this user+phone, else create a placeholder.
        $account = SourceAccount::firstOrCreate(
            [
                'user_id'    => auth()->id(),
                'type'       => SourceAccount::TYPE_TELEGRAM,
                'identifier' => $phone,
            ],
            [
                'name'        => 'Telegram – ' . $phone,
                'enabled'     => false,
                'credentials' => [],
            ],
        );

        try {
            $auth = TelegramUserAuth::forAccountId($account->id);
            $res = $auth->start($phone);
        } catch (\Throwable $e) {
            $account->delete();
            return back()->withErrors(['source' => 'Telegram login failed: ' . $e->getMessage()]);
        }

        $account->update([
            'credentials' => [
                'mtproto'         => true,
                'mtproto_state'   => 'awaiting_code',
                'phone'           => $phone,
                'phone_code_hash' => $res['phone_code_hash'] ?? null,
            ],
        ]);

        return back()->with('status', 'SMS code sent to ' . $phone . '. Enter it below to finish login.');
    }

    public function telegramUserCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account_id' => 'required|integer',
            'code'       => 'required|string|min:3|max:32',
        ]);
        $account = SourceAccount::findOrFail($data['account_id']);
        $this->authorizeAccount($account);

        try {
            $auth = TelegramUserAuth::forAccountId($account->id);
            $res = $auth->submitCode(trim($data['code']));
        } catch (\Throwable $e) {
            return back()->withErrors(['source' => 'Code rejected: ' . $e->getMessage()]);
        }

        if (($res['status'] ?? '') === 'needs_password') {
            $account->update([
                'credentials' => array_merge($account->credentials ?? [], [
                    'mtproto_state' => 'awaiting_password',
                ]),
            ]);
            return back()->with('status', 'Two-factor password required.');
        }

        return $this->finalizeTelegramUser($account);
    }

    public function telegramUserPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account_id' => 'required|integer',
            'password'   => 'required|string|min:1|max:512',
        ]);
        $account = SourceAccount::findOrFail($data['account_id']);
        $this->authorizeAccount($account);

        try {
            $auth = TelegramUserAuth::forAccountId($account->id);
            $auth->submitPassword($data['password']);
        } catch (\Throwable $e) {
            return back()->withErrors(['source' => '2FA password rejected: ' . $e->getMessage()]);
        }

        return $this->finalizeTelegramUser($account);
    }

    public function telegramUserCancel(SourceAccount $source): RedirectResponse
    {
        $this->authorizeAccount($source);
        try {
            TelegramUserAuth::forAccountId($source->id)->logoutAndDelete();
        } catch (\Throwable) {
            // ignore
        }
        $source->delete();
        return redirect()->route('sources.index')->with('status', 'Telegram login cancelled.');
    }

    protected function finalizeTelegramUser(SourceAccount $account): RedirectResponse
    {
        try {
            $auth = TelegramUserAuth::forAccountId($account->id);
            $self = $auth->me();
        } catch (\Throwable $e) {
            return back()->withErrors(['source' => 'Telegram session check failed: ' . $e->getMessage()]);
        }

        $username = $self['username'] ?? null;
        $name = trim(($self['first_name'] ?? '') . ' ' . ($self['last_name'] ?? ''))
            ?: ($username ? '@' . $username : ($self['phone'] ?? 'Telegram user'));
        $identifier = $username ? '@' . $username : ($self['phone'] ?? (string) $self['id']);

        $account->update([
            'name'        => "Telegram \u{2013} {$name}",
            'identifier'  => $identifier,
            'enabled'     => true,
            'credentials' => array_merge($account->credentials ?? [], [
                'mtproto'        => true,
                'mtproto_state'  => 'connected',
                'tg_user_id'     => $self['id'] ?? null,
                'tg_username'    => $username,
                'tg_first_name'  => $self['first_name'] ?? null,
                'phone_code_hash'=> null,
            ]),
        ]);

        return redirect()->route('sources.index')->with('status', 'Telegram connected as ' . $name);
    }
}
