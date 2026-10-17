<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminGuestRedirect
{
    public function handle(Request $request, Closure $next)
    {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
