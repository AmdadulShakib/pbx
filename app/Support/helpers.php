<?php

use Illuminate\Support\Facades\File;

if (!function_exists('displayImage')) {
    function displayImage(?string $path, string $default = 'assets/images/default.jpg'): string
    {
        $path = is_string($path) ? trim($path) : '';
        $path = ltrim($path, '/');

        if ($path === '') {
            $path = ltrim($default, '/');
        }

        $fullPath = public_path($path);
        if (!File::exists($fullPath)) {
            $path = ltrim($default, '/');
        }

        return asset('' . $path);
    }
}

if (!function_exists('isWebEnabledModule')) {
    function isWebEnabledModule(string $moduleKey): bool
    {
        $modules = config('dynamic_modules', []);
        if (!is_array($modules)) {
            return false;
        }
        $module = $modules[$moduleKey] ?? null;
        if (!is_array($module)) {
            return false;
        }

        return !empty($module['web_routes'])
            || !empty($module['web_controller'])
            || !empty($module['views']);
    }
}

if (!function_exists('nav_is_active')) {
    function nav_is_active($patterns): bool
    {
        if (is_string($patterns)) {
            $patterns = [$patterns];
        }
        if (!is_array($patterns)) {
            return false;
        }
        $patterns = array_values(array_filter($patterns, function ($pattern) {
            return is_string($pattern) && trim($pattern) !== '';
        }));
        if (empty($patterns)) {
            return false;
        }

        return request()->routeIs(...$patterns);
    }
}

if (!function_exists('nav_active')) {
    function nav_active($patterns, string $class = 'active'): string
    {
        return nav_is_active($patterns) ? $class : '';
    }
}

if (!function_exists('nav_open')) {
    function nav_open($patterns, string $class = 'show'): string
    {
        return nav_is_active($patterns) ? $class : '';
    }
}

