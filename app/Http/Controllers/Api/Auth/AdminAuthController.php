<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $typeColumn = $this->resolveUserTypeColumn((new User())->getTable());
        if ($typeColumn === null) {
            return response()->json([
                'success' => false,
                'message' => 'User type column not found.',
            ], 500);
        }

        $user = User::where('email', $credentials['email'])
            ->where($typeColumn, 'admin')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $token = $user->createToken('admin-token', ['admin'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    protected function resolveUserTypeColumn(string $table): ?string
    {
        if (Schema::hasColumn($table, 'user_type')) {
            return 'user_type';
        }
        if (Schema::hasColumn($table, 'type')) {
            return 'type';
        }
        if (Schema::hasColumn($table, 'role')) {
            return 'role';
        }

        return null;
    }
}
