<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Public\LandingController;
use App\Http\Controllers\Web\User\Auth\LoginController;
use App\Http\Controllers\Web\User\Auth\RegisterController;
use App\Http\Controllers\Web\User\Auth\PasswordController;

Route::get('/', [LandingController::class, 'index']);

Route::controller(LoginController::class)->group(function() {
    Route::get('login', 'showLoginForm')->name('login');
    Route::post('login', 'login');
});

Route::controller(RegisterController::class)->group(function() {
    Route::get('register', 'showRegisterForm')->name('register');
    Route::post('register', 'register');
});

Route::controller(PasswordController::class)->group(function() {
    Route::get('forgot-password', 'showForgotForm')->name('password.request');
    Route::post('forgot-password', 'sendResetLink');
});

//AllDynamicDataStart
//AllDynamicDataEnd
