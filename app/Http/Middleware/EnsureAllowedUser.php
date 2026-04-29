<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAllowedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isAllowed()) {
            if ($request->expectsJson()) {
                abort(403, 'This account is not allowed to use AI Inbox.');
            }
            auth()->guard('web')->logout();
            $request->session()?->invalidate();
            $request->session()?->regenerateToken();
            return redirect('/login')->withErrors([
                'email' => 'This Google account is not allowed.',
            ]);
        }
        return $next($request);
    }
}
