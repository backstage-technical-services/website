<?php

use App\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/auth', [AuthController::class, 'redirect'])->name('auth.login');

Route::get('/auth/callback', [AuthController::class, 'callback'])->name('auth.callback');

Route::get('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
