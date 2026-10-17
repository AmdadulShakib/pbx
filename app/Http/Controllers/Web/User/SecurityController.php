<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        return view('user.security', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        if (!$user) {
            return back()->with('error', __('Unable to update password.'));
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        $request->session()->regenerateToken();

        return back()->with('success', __('Password updated successfully.'));
    }
}
