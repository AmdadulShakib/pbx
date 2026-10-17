<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\User\DashboardController;

Route::get('home', [DashboardController::class, 'index'])->name('home');
