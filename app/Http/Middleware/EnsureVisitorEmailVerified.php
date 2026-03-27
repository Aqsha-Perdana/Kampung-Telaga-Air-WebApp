<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVisitorEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your account must be verified before continuing.',
            ], 403);
        }

        return redirect()
            ->route('wisatawan.verification.notice')
            ->with('warning', 'Please verify your account before continuing.');
    }
}
