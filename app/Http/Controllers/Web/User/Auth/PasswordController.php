<?php

namespace App\Http\Controllers\Web\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('user.auth.forgot');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        return back()->with('success', __('If your email exists, a reset link will be sent.'));
    }
}
