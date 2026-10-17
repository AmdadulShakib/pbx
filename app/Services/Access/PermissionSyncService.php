<?php

namespace App\Services\Access;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PermissionSyncService
{
    protected array $skipRoutes = [
        'admin.login',
        'admin.logout',
        'admin.home',
        'admin.no-access',
    ];

    public function sync(): array
    {
        if (!Schema::hasTable('permissions')) {
            return [];
        }

        $routes = Route::getRoutes();
        $keys = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            if (!is_string($name) || $name === '') {
                continue;
            }
            if (!Str::startsWith($name, 'admin.')) {
                continue;
            }
            if (in_array($name, $this->skipRoutes, true)) {
                continue;
            }

            [$group, $action] = $this->splitName($name);
            $label = Str::headline($group) . ' - ' . Str::headline($action);

            Permission::updateOrCreate(
                ['key' => $name],
                [
                    'name' => $label,
                    'group' => $group,
                    'action' => $action,
                    'scope' => 'admin',
                    'is_dynamic' => true,
                ]
            );
            $keys[] = $name;
        }

        $this->ensureSuperRole();

        return $keys;
    }

    protected function splitName(string $name): array
    {
        $trimmed = Str::after($name, 'admin.');
        $parts = explode('.', $trimmed);
        $group = $parts[0] ?? 'general';
        $action = $parts[1] ?? 'view';

        return [$group, $action];
    }

    protected function ensureSuperRole(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $role = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full access to all admin features.',
                'is_super' => true,
            ]
        );

        if (!Schema::hasTable('role_user')) {
            return;
        }

        $hasAssignments = $role->users()->exists();
        if ($hasAssignments) {
            return;
        }

        $admin = $this->firstAdminUser();
        if ($admin) {
            $admin->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    protected function firstAdminUser(): ?User
    {
        $user = new User();
        $table = $user->getTable();
        $typeColumn = null;
        foreach (['user_type', 'type', 'role'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $typeColumn = $column;
                break;
            }
        }

        $query = User::query();
        if ($typeColumn) {
            $query->where($typeColumn, 'admin');
        }

        return $query->first();
    }
}
