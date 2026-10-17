<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminHomeRedirect
{
    public function handle(Request $request, Closure $next)
    {
        $path = trim($request->path(), '/');
        if ($request->isMethod('GET') && $path === 'admin') {
            if (auth('admin')->check()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
