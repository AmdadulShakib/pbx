<?php

namespace App\Http\Controllers\Web\Admin\Access;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Access\PermissionSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index(PermissionSyncService $syncService)
    {
        if (!Schema::hasTable('roles')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', __('Access control tables are missing. Run migrations first.'));
        }

        $syncService->sync();

        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();

        return view('admin.access.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create(PermissionSyncService $syncService)
    {
        if (!Schema::hasTable('roles')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', __('Access control tables are missing. Run migrations first.'));
        }

        $groups = $this->permissionGroups($syncService);

        return view('admin.access.roles.create', [
            'role' => new Role(),
            'permissionGroups' => $groups,
            'selected' => [],
        ]);
    }

    public function store(Request $request, PermissionSyncService $syncService)
    {
        if (!Schema::hasTable('roles')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', __('Access control tables are missing. Run migrations first.'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_super' => ['nullable', 'boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['integer'],
        ]);

        $slug = Str::slug($data['name']);
        $role = Role::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'is_super' => !empty($data['is_super']),
        ]);

        $this->syncPermissions($role, $data['permissions'] ?? [], $syncService);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('Role created.'));
    }

    public function edit(Role $role, PermissionSyncService $syncService)
    {
        if (!Schema::hasTable('roles')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', __('Access control tables are missing. Run migrations first.'));
        }

        $groups = $this->permissionGroups($syncService);
        $selected = $role->permissions()->pluck('permissions.id')->all();

        return view('admin.access.roles.edit', [
            'role' => $role,
            'permissionGroups' => $groups,
            'selected' => $selected,
        ]);
    }

    public function update(Request $request, Role $role, PermissionSyncService $syncService)
    {
        if (!Schema::hasTable('roles')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', __('Access control tables are missing. Run migrations first.'));
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_super' => ['nullable', 'boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['integer'],
        ]);

        $role->name = $data['name'];
        $role->slug = Str::slug($data['name']);
        $role->description = $data['description'] ?? null;
        $role->is_super = !empty($data['is_super']);
        $role->save();

        $this->syncPermissions($role, $data['permissions'] ?? [], $syncService);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('Role updated.'));
    }

    public function destroy(Role $role)
    {
        if (!Schema::hasTable('roles')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', __('Access control tables are missing. Run migrations first.'));
        }

        if ($role->is_super) {
            return back()->with('error', __('Super admin role cannot be deleted.'));
        }

        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();

        return back()->with('success', __('Role removed.'));
    }

    protected function permissionGroups(PermissionSyncService $syncService): iterable
    {
        $syncService->sync();

        if (!Schema::hasTable('permissions')) {
            return [];
        }

        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('action')
            ->get();

        $priority = [
            'dashboard' => ['order' => 0, 'label' => 'Dashboard'],
            'users' => ['order' => 1, 'label' => 'User Management'],
            'business-settings' => ['order' => 2, 'label' => 'Business Settings'],
            'staff' => ['order' => 3, 'label' => 'Access Control'],
            'roles' => ['order' => 3, 'label' => 'Access Control'],
        ];

        $groups = [];
        foreach ($permissions as $permission) {
            $group = $permission->group ?: 'general';
            $label = Str::headline($group);
            $order = 50;
            $key = $group;

            if (isset($priority[$group])) {
                $label = $priority[$group]['label'];
                $order = $priority[$group]['order'];
                if (in_array($group, ['staff', 'roles'], true)) {
                    $key = 'access-control';
                }
            }

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'label' => $label,
                    'order' => $order,
                    'permissions' => [],
                ];
            }

            $groups[$key]['permissions'][] = $permission;
        }

        $groupList = array_values($groups);
        usort($groupList, function ($left, $right) {
            if ($left['order'] === $right['order']) {
                return strcmp($left['label'], $right['label']);
            }
            return $left['order'] <=> $right['order'];
        });

        return $groupList;
    }

    protected function syncPermissions(Role $role, array $permissions, PermissionSyncService $syncService): void
    {
        if ($role->is_super) {
            $role->permissions()->detach();
            return;
        }

        $syncService->sync();

        if (Schema::hasTable('role_permission') && Schema::hasTable('permissions')) {
            $role->permissions()->sync($permissions);
        }
    }
}
