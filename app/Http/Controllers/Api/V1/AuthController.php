<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/google
     * Body: { "id_token": "...", "device_name": "iPhone" }
     *
     * Verifies a Google ID token (mobile sign-in flow), enforces the allowed
     * email, then issues a Sanctum personal-access token.
     */
    public function google(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_token' => 'required|string',
            'device_name' => 'sometimes|string|max:100',
        ]);

        $resp = Http::get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $data['id_token']]);
        if (! $resp->successful()) {
            throw ValidationException::withMessages(['id_token' => ['Invalid Google id_token.']]);
        }
        $info = $resp->json();
        $email = strtolower((string) ($info['email'] ?? ''));
        $emailVerified = ($info['email_verified'] ?? 'false') === true || $info['email_verified'] === 'true';

        if (! $email || ! $emailVerified) {
            throw ValidationException::withMessages(['id_token' => ['Email not verified.']]);
        }
        if ($email !== strtolower((string) config('inbox.allowed_email'))) {
            return response()->json(['message' => 'This Google account is not allowed.'], 403);
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $info['name'] ?? 'User',
                'google_id' => $info['sub'] ?? null,
                'avatar_url' => $info['picture'] ?? null,
                'email_verified_at' => now(),
            ],
        );

        $token = $user->createToken($data['device_name'] ?? 'mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'avatar_url' => $user->avatar_url],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();
        return response()->json(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        $u = $request->user();
        return response()->json([
            'id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'avatar_url' => $u->avatar_url,
        ]);
    }
}
