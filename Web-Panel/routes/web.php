<?php

use App\Http\Controllers\AdministratorUserController;
use App\Http\Controllers\ArchivedEmployeeManagementController;
use App\Http\Controllers\EmployeeManagementController;
use App\Http\Controllers\UserSessionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [UserSessionController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserSessionController::class, 'createSession'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::post('/logout', [UserSessionController::class, 'destroySession'])->name('logout');
});

Route::middleware(['auth', 'can:administrator.panel'])
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

Route::middleware(['auth', 'can:employees.manage.view'])
    ->prefix('employees')
    ->name('employees.')
    ->group(function (): void {
        Route::get('/', [EmployeeManagementController::class, 'index'])->name('index');

        Route::get('/create', [EmployeeManagementController::class, 'create'])
            ->middleware('can:employees.manage.create')
            ->name('create');

        Route::post('/', [EmployeeManagementController::class, 'store'])
            ->middleware('can:employees.manage.create')
            ->name('store');

        Route::get('/{employee}/edit', [EmployeeManagementController::class, 'edit'])
            ->middleware('can:employees.manage.update')
            ->name('edit');

        Route::put('/{employee}', [EmployeeManagementController::class, 'update'])
            ->middleware('can:employees.manage.update')
            ->name('update');

        Route::delete('/{employee}', [EmployeeManagementController::class, 'destroy'])
            ->middleware('can:employees.manage.archive')
            ->name('archive');

        Route::get('/archived', [ArchivedEmployeeManagementController::class, 'index'])
            ->middleware('can:employees.manage.restore')
            ->name('archived');

        Route::post('/archived/{employeeId}/restore', [ArchivedEmployeeManagementController::class, 'restore'])
            ->middleware('can:employees.manage.restore')
            ->name('restore');
    });
