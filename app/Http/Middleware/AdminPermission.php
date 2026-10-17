<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Services\Access\PermissionSyncService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class AdminPermission
{
    protected array $skipNames = [
        'admin.login',
        'admin.logout',
        'admin.home',
        'admin.no-access',
    ];

    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $name = $route ? $route->getName() : null;

        if (!$name || in_array($name, $this->skipNames, true)) {
            return $next($request);
        }

        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
            return $next($request);
        }

        if (!Permission::query()->where('key', $name)->exists()) {
            app(PermissionSyncService::class)->sync();
        }

        if (!Permission::query()->where('key', $name)->exists()) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && Gate::forUser($user)->allows($name)) {
            return $next($request);
        }

        if (Route::has('admin.no-access')) {
            return redirect()
                ->route('admin.no-access')
                ->with('error', __('Access denied.'));
        }

        abort(403, __('Access denied.'));
    }
}
