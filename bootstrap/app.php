<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders(array_filter([
        class_exists(\Laravel\Sanctum\SanctumServiceProvider::class) ? \Laravel\Sanctum\SanctumServiceProvider::class : null,
    ]))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/web/web.php',
            __DIR__.'/../routes/web/user.php',
            __DIR__.'/../routes/web/admin.php',
        ],
        api: [
            __DIR__.'/../routes/api/v1/api.php',
            __DIR__.'/../routes/api/v1/admin.php',
            __DIR__.'/../routes/api/v1/user.php',
        ],
        apiPrefix: 'api/v1',
        health: '/up',
    )->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            
            'admin.home.redirect' => \App\Http\Middleware\AdminHomeRedirect::class,

            'admin.api' => \App\Http\Middleware\AdminApi::class,

            'user.api' => \App\Http\Middleware\UserApi::class,

            'admin.permission' => \App\Http\Middleware\AdminPermission::class,
'admin' => App\Http\Middleware\RedirectIfNotAdmin::class,
            'admin.guest' => \App\Http\Middleware\RedirectIfAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
