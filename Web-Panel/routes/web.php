<?php

use App\Http\Controllers\UserSessionController;
use App\Http\Controllers\AdministratorUserController;
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

Route::middleware(['auth', 'can:access-administrator-panel'])
    ->prefix('administrator')
    ->name('administrator.')
    ->group(function (): void {
        Route::get('/users', [AdministratorUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdministratorUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdministratorUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdministratorUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdministratorUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdministratorUserController::class, 'destroy'])->name('users.destroy');
    });
