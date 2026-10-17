<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $fields = $this->availableFields($user);
        $completion = $this->profileCompletion($user, $fields);

        return view('user.profile', [
            'user' => $user,
            'fields' => $fields,
            'meta' => [
                'completion' => $completion,
                'member_since' => $user?->created_at?->format('M d, Y') ?? 'Not available',
                'last_updated' => $user?->updated_at?->diffForHumans() ?? 'Not available',
                'email_verified' => $fields['email_verified_at'] && !empty($user?->email_verified_at),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return back()->with('error', __('Unable to update profile.'));
        }

        $table = $user->getTable();
        $rules = [];
        if (Schema::hasColumn($table, 'name')) {
            $rules['name'] = ['required', 'string', 'max:255'];
        }
        if (Schema::hasColumn($table, 'email')) {
            $rules['email'] = [
                'required',
                'email',
                'max:255',
                Rule::unique($table, 'email')->ignore($user->getKey()),
            ];
        }
        if (Schema::hasColumn($table, 'phone')) {
            $rules['phone'] = ['nullable', 'string', 'max:50'];
        }
        if (Schema::hasColumn($table, 'address')) {
            $rules['address'] = ['nullable', 'string', 'max:255'];
        }
        if (Schema::hasColumn($table, 'avatar')) {
            $rules['avatar'] = ['nullable', 'string', 'max:255'];
        }

        if (empty($rules)) {
            return back()->with('error', __('No updatable fields found.'));
        }

        $data = $request->validate($rules);
        foreach ($data as $key => $value) {
            $user->{$key} = $value;
        }
        $user->save();

        return back()->with('success', __('Profile updated successfully.'));
    }

    protected function availableFields($user): array
    {
        if (!$user) {
            return [
                'name' => false,
                'email' => false,
                'phone' => false,
                'address' => false,
                'avatar' => false,
                'email_verified_at' => false,
            ];
        }

        $table = $user->getTable();
        return [
            'name' => Schema::hasColumn($table, 'name'),
            'email' => Schema::hasColumn($table, 'email'),
            'phone' => Schema::hasColumn($table, 'phone'),
            'address' => Schema::hasColumn($table, 'address'),
            'avatar' => Schema::hasColumn($table, 'avatar'),
            'email_verified_at' => Schema::hasColumn($table, 'email_verified_at'),
        ];
    }

    protected function profileCompletion($user, array $fields): int
    {
        if (!$user) {
            return 0;
        }

        $track = ['name', 'email', 'phone', 'address', 'avatar'];
        $total = 0;
        $filled = 0;
        foreach ($track as $field) {
            if (!($fields[$field] ?? false)) {
                continue;
            }
            $total++;
            if (!empty($user->{$field})) {
                $filled++;
            }
        }

        if ($total === 0) {
            return 100;
        }

        return (int) round(($filled / $total) * 100);
    }
}
