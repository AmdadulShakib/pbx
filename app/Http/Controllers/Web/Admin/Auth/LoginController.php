<?php

namespace App\Http\Controllers\Web\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $authenticated = Auth::guard('admin')->attempt($credentials, $request->boolean('remember'));
        } catch (\RuntimeException $e) {
            return back()
                ->with('error', __('Invalid credentials.'))
                ->withInput();
        }

        if ($authenticated) {
            $request->session()->regenerate();

            return redirect()
                ->to($this->resolveRedirectRoute($request))
                ->with('success', __('Welcome back!'));
        }

        return back()
            ->with('error', __('Invalid credentials.'))
            ->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login')
            ->with('success', __('Logged out successfully.'));
    }

    protected function resolveRedirectRoute(Request $request): string
    {
        $user = $request->user('admin');
        if (!$user || !method_exists($user, 'hasPermission')) {
            return route('admin.dashboard');
        }

        $preferredRoutes = [
            'admin.dashboard',
            'admin.users.index',
            'admin.business-settings.general',
            'admin.staff.index',
            'admin.roles.index',
        ];

        foreach ($preferredRoutes as $route) {
            if (Route::has($route) && $user->hasPermission($route)) {
                return route($route);
            }
        }

        $menuRoute = $this->firstAllowedRoute(config('dynamic_sidebar', []), $user);
        if ($menuRoute && Route::has($menuRoute)) {
            return route($menuRoute);
        }

        return route('admin.no-access');
    }

    protected function firstAllowedRoute(array $menu, $user): ?string
    {
        foreach ($menu as $node) {
            if (!is_array($node)) {
                continue;
            }
            $type = $node['type'] ?? null;
            if ($type === 'group') {
                $route = $this->firstAllowedRoute($node['items'] ?? [], $user);
                if ($route) {
                    return $route;
                }
                continue;
            }
            if (!in_array($type, ['module', 'route'], true)) {
                continue;
            }
            $route = $node['route'] ?? null;
            if (!is_string($route) || $route === '') {
                continue;
            }
            if (Route::has($route) && $user->hasPermission($route)) {
                return $route;
            }
            if (Str::endsWith($route, '.index')) {
                $fallback = Str::beforeLast($route, '.') . '.create';
                if (Route::has($fallback) && $user->hasPermission($fallback)) {
                    return $fallback;
                }
            }
        }

        return null;
    }
}
