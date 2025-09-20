<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCustomer
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || $request->user()->role !== 'customer') {
            return response()->json(['message' => 'Hanya customer yang bisa mengakses'], 403);
        }

        return $next($request);
    }
}
