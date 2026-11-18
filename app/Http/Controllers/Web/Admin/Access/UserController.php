<?php

namespace App\Http\Controllers\Web\Admin\Access;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $typeColumn = $this->resolveTypeColumn();

        $query = User::query();
        if ($typeColumn) {
            $query->where($typeColumn, 'user');
        }
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.access.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function create()
    {
        return view('admin.access.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
            $path = public_path('uploads/avatars');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $avatar->move($path, $filename);
            $user->avatar = 'uploads/avatars/' . $filename;
        }

        $typeColumn = $this->resolveTypeColumn();
        if ($typeColumn) {
            $user->{$typeColumn} = 'user';
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('User created.'));
    }

    public function edit(User $user)
    {
        return view('admin.access.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
            $path = public_path('uploads/avatars');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $avatar->move($path, $filename);
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }
            $user->avatar = 'uploads/avatars/' . $filename;
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('User updated.'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', __('User removed.'));
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
