<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Google-only auth. Password endpoints are intentionally removed.
Route::middleware('guest')->group(function () {
    Route::get('login', fn () => Inertia::render('Auth/Login', [
        'canResetPassword' => false,
        'status' => session('status'),
    ]))->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
