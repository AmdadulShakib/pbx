<?php

use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Auth\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function() {
    Route::prefix('auth')->controller(AdminAuthController::class)->group(function() {
        Route::post('login', 'login');
    });

    Route::middleware(['api', 'auth:sanctum', 'admin.api', 'throttle:120,1'])->group(function() {
        Route::prefix('auth')->controller(AdminAuthController::class)->group(function() {
            Route::post('logout', 'logout');
            Route::get('me', 'me');
        });

        //AllDynamicDataStart
        Route::prefix('category')->group(function() {
            Route::controller(CategoryController::class)->group(function() {
                Route::get('/', 'index');
                Route::get('{id}', 'show');
                Route::post('/', 'store');
                Route::match(['put', 'patch'], '{id}', 'update');
                Route::delete('{id}', 'destroy');
            });
        });
        //AllDynamicDataEnd
    });
});
