<?php

use App\Http\Controllers\UserSessionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [UserSessionController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserSessionController::class, 'createSession']);
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::post('/logout', [UserSessionController::class, 'destroySession'])->name('logout');
});
