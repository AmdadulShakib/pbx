<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminApi
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $role = $user->user_type ?? $user->type ?? $user->role ?? null;
        if (!$user || $role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!$request->user()->tokenCan('admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
