<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isExpired()) {
            return response()->json([
                'message' => __('auth.account_expired'),
            ], 403);
        }

        return $next($request);
    }
}
