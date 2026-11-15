<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PbxAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $admins = array_filter(array_map('trim', explode(',', (string) config('pbx.admin_emails'))));
        $user   = $request->user('admin') ?: $request->user();

        if (!$user) {
            abort(403, 'shudu pbx adminder jonno');
        }

        // If specific admin emails configured, allow if in list
        if (!empty($admins) && in_array($user->email, $admins, true)) {
            return $next($request);
        }

        // Allow any admin user or super admin
        $type = $user->user_type ?? $user->type ?? $user->role ?? null;
        if ($type === 'admin' || (method_exists($user, 'roles') && $user->roles()->where('is_super', true)->exists())) {
            return $next($request);
        }

        // If no strict admin list defined, allow authenticated admin
        if (empty($admins)) {
            return $next($request);
        }

        abort(403, 'shudu pbx adminder jonno');
    }
}
