<?php

use App\Http\Controllers\Web\Admin;
use App\Http\Controllers\Web\Admin\Access\RoleController;
use App\Http\Controllers\Web\Admin\Access\StaffController;
use App\Http\Controllers\Web\Admin\Access\UserController;
use App\Http\Controllers\Web\Admin\AdminController;
use App\Http\Controllers\Web\Admin\Auth\LoginController;
use App\Http\Controllers\Web\Admin\BusinessSettingsController;
use App\Http\Controllers\Web\Admin\CategoryController;
use App\Http\Controllers\Web\Admin\PbxExtensionController;
use App\Http\Controllers\Web\Admin\PbxStatusController;
use App\Http\Middleware\PbxAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'home'])->middleware('admin.home.redirect')->name('home');

    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->name('logout');

    });

    Route::middleware(['auth:admin', 'admin.permission'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
                Route::name('business-settings.')->prefix('business-settings')->group(function() {
            Route::controller(BusinessSettingsController::class)->group(function() {
                Route::get('/', 'index')->name('index');
                Route::get('general', 'general')->name('general');
                Route::post('general', 'saveGeneral')->name('general.save');
                Route::get('branding', 'branding')->name('branding');
                Route::post('branding', 'saveBranding')->name('branding.save');
                Route::get('advanced', 'advanced')->name('advanced');
            });
        });
        Route::name('users.')->prefix('users')->controller(UserController::class)->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{user}/edit', 'edit')->name('edit');
            Route::put('{user}', 'update')->name('update');
            Route::delete('{user}', 'destroy')->name('destroy');
        });

        Route::name('staff.')->prefix('staff')->controller(StaffController::class)->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{staff}/edit', 'edit')->name('edit');
            Route::put('{staff}', 'update')->name('update');
            Route::delete('{staff}', 'destroy')->name('destroy');
        });

        Route::name('roles.')->prefix('roles')->controller(RoleController::class)->group(function() {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{role}/edit', 'edit')->name('edit');
            Route::put('{role}', 'update')->name('update');
            Route::delete('{role}', 'destroy')->name('destroy');
        });

        //AllDynamicDataStart
                
        Route::name('category.')->prefix('category')->group(function() {
            Route::controller(CategoryController::class)->group(function() {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{id}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '{id}', 'update')->name('update');
                Route::delete('{id}', 'destroy')->name('destroy');
            });
        });

        Route::middleware(PbxAdmin::class)->prefix('pbx')->name('pbx.')->group(function () {
            Route::resource('extensions', PbxExtensionController::class)->except('show');
            Route::get('status', [PbxStatusController::class, 'index'])->name('status');
            Route::post('originate', [PbxStatusController::class, 'originate'])->name('originate');
        });
    });
});
