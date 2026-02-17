<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Users\UserManagementController;
use App\Http\Controllers\Users\UserSessionController;
use App\Http\Controllers\Departments\DepartmentManagementController;
use App\Http\Controllers\Employees\ArchivedEmployeeManagementController;
use App\Http\Controllers\Employees\EmployeeManagementController;
use App\Http\Controllers\Attendance\AttendanceChangelogController;
use App\Http\Controllers\Attendance\AttendanceDeviceManagementController;
use App\Http\Controllers\Attendance\AttendancePresentController;
use App\Http\Controllers\Attendance\AttendanceEvacuationListPrintController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [UserSessionController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserSessionController::class, 'createSession'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/logout', [UserSessionController::class, 'destroySession'])->name('logout');
});

Route::middleware(['auth', 'can:users.manage'])
    ->prefix('users')
    ->name('users.')
    ->group(function (): void {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth', 'can:administrator.only'])
    ->prefix('administrator/attendance-devices')
    ->name('administrator.attendance-devices.')
    ->group(function (): void {
        Route::get('/', [AttendanceDeviceManagementController::class, 'index'])->name('index');
        Route::get('/create', [AttendanceDeviceManagementController::class, 'create'])->name('create');
        Route::post('/', [AttendanceDeviceManagementController::class, 'store'])->name('store');

        Route::get('/{attendanceDevice}/edit', [AttendanceDeviceManagementController::class, 'edit'])->name('edit');
        Route::put('/{attendanceDevice}', [AttendanceDeviceManagementController::class, 'update'])->name('update');

        Route::post('/{attendanceDevice}/rotate-token', [AttendanceDeviceManagementController::class, 'rotateToken'])->name('rotate-token');

        Route::patch('/{attendanceDevice}/activate', [AttendanceDeviceManagementController::class, 'activate'])->name('activate');
        Route::patch('/{attendanceDevice}/deactivate', [AttendanceDeviceManagementController::class, 'deactivate'])->name('deactivate');
    });
    
Route::middleware(['auth', 'can:departments.manage'])
    ->prefix('departments')
    ->name('departments.')
    ->group(function (): void {
        Route::get('/', [DepartmentManagementController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentManagementController::class, 'create'])->name('create');
        Route::post('/', [DepartmentManagementController::class, 'store'])->name('store');
        Route::get('/{department}/edit', [DepartmentManagementController::class, 'edit'])->name('edit');
        Route::put('/{department}', [DepartmentManagementController::class, 'update'])->name('update');
        Route::delete('/{department}', [DepartmentManagementController::class, 'destroy'])->name('destroy');
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

Route::middleware(['auth'])
    ->prefix('attendance')
    ->name('attendance.')
    ->group(function (): void {
        Route::get('/present', AttendancePresentController::class)
            ->middleware('can:attendance.present.view')
            ->name('present');

        Route::get('/present/print', AttendanceEvacuationListPrintController::class)
            ->middleware('can:attendance.present.print')
            ->name('present.print');

        Route::get('/changelog', AttendanceChangelogController::class)
            ->middleware('can:attendance.changelog.view')
            ->name('changelog');
    });
