<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PbxAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $admins = array_filter(array_map('trim', explode(',', (string) config('pbx.admin_emails'))));
        $user   = $request->user();

        abort_unless($user && in_array($user->email, $admins, true), 403, 'shudu pbx adminder jonno');

        return $next($request);
    }
}