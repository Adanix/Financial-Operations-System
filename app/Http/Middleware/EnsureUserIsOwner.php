<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isOwner()) {
            if ($request->expectsJson() || !$request->isMethodSafe()) {
                abort(403, 'Permission denied: Reviewer (viewer) account is read-only and cannot mutate records.');
            }

            return redirect()->back()->with('error', 'Reviewer account is in read-only mode and cannot perform modifications.');
        }

        return $next($request);
    }
}
