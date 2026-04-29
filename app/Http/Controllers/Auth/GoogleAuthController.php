<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

class GoogleAuthController extends Controller
{
    public function redirect(): SymfonyRedirect
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect('/login')->withErrors(['email' => 'Google sign-in failed.']);
        }

        $allowed = strtolower((string) config('inbox.allowed_email'));
        if (strtolower((string) $googleUser->getEmail()) !== $allowed) {
            return redirect('/login')->withErrors([
                'email' => 'This Google account is not allowed.',
            ]);
        }

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'User',
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ],
        );

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended('/dashboard');
    }
}
