<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\User\Auth\LoginController;
use App\Http\Controllers\Web\User\DashboardController;
use App\Http\Controllers\Web\User\ProfileController;
use App\Http\Controllers\Web\User\SecurityController;
use App\Http\Controllers\Web\User\PbxPhoneController;

Route::prefix('user')->name('user.')->group(function() {
    Route::middleware(['auth:web,admin'])->group(function() {
        Route::prefix('pbx')->name('pbx.')->group(function () {
            Route::get('phone', [PbxPhoneController::class, 'index'])->name('phone');
            Route::get('phone-window', [PbxPhoneController::class, 'window'])->name('phone.window');
            Route::get('history', [PbxPhoneController::class, 'history'])->name('history');
            Route::get('phone/credentials', [PbxPhoneController::class, 'credentials'])->name('phone.credentials');
            Route::post('call-log', [\App\Http\Controllers\Web\Admin\PbxCallLogController::class, 'store'])->name('call-log.store');
        });
    });

    Route::middleware(['auth:web'])->group(function() {
        Route::controller(LoginController::class)->group(function() {
            Route::post('logout', 'logout')->name('logout');
        });

        Route::get('/', [DashboardController::class, 'index'])->name('home');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('security', [SecurityController::class, 'index'])->name('security');
        Route::put('security', [SecurityController::class, 'update'])->name('security.update');

        //AllDynamicDataStart
        //AllDynamicDataEnd
    });
});
