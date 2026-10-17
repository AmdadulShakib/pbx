<?php

namespace App\Http\Controllers\Web\Admin\Access;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $typeColumn = $this->resolveTypeColumn();

        $rolesEnabled = Schema::hasTable('role_user') && Schema::hasTable('roles');
        $query = User::query();
        if ($rolesEnabled) {
            $query->with('roles');
        }
        if ($typeColumn) {
            $query->where($typeColumn, 'admin');
        }
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $staff = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.access.staff.index', [
            'staff' => $staff,
            'search' => $search,
            'rolesEnabled' => $rolesEnabled,
        ]);
    }

    public function create()
    {
        $roles = Schema::hasTable('roles') ? Role::orderBy('name')->get() : collect();

        return view('admin.access.staff.create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['integer'],
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);

        $typeColumn = $this->resolveTypeColumn();
        if ($typeColumn) {
            $user->{$typeColumn} = 'admin';
        }

        $user->save();

        if (Schema::hasTable('role_user') && !empty($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }

        return redirect()
            ->route('admin.staff.index')
            ->with('success', __('Staff member created.'));
    }

    public function edit(User $staff)
    {
        $roles = Schema::hasTable('roles') ? Role::orderBy('name')->get() : collect();
        $assigned = Schema::hasTable('role_user')
            ? $staff->roles()->pluck('roles.id')->all()
            : [];

        return view('admin.access.staff.edit', [
            'staff' => $staff,
            'roles' => $roles,
            'assigned' => $assigned,
        ]);
    }

    public function update(Request $request, User $staff)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff->id)],
            'password' => ['nullable', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['integer'],
        ]);

        $staff->name = $data['name'];
        $staff->email = $data['email'];
        if (!empty($data['password'])) {
            $staff->password = Hash::make($data['password']);
        }
        $staff->save();

        if (Schema::hasTable('role_user')) {
            $staff->roles()->sync($data['roles'] ?? []);
        }

        return redirect()
            ->route('admin.staff.index')
            ->with('success', __('Staff member updated.'));
    }

    public function destroy(Request $request, User $staff)
    {
        if ($request->user()?->id === $staff->id) {
            return back()->with('error', __('You cannot delete your own account.'));
        }

        $staff->delete();

        return back()->with('success', __('Staff member removed.'));
    }

    protected function resolveTypeColumn(): ?string
    {
        $table = (new User())->getTable();
        foreach (['user_type', 'type', 'role'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }
}
