<?php

use App\Http\Controllers\Api\AttendancePingController;
use App\Http\Controllers\Api\AttendanceTapController;
use App\Http\Middleware\AuthenticateAttendanceDevice;
use Illuminate\Support\Facades\Route;

Route::prefix('attendance')
    ->middleware([
        AuthenticateAttendanceDevice::class,
        'throttle:attendance-device',
    ])
    ->group(function (): void {
        Route::post('/tap', AttendanceTapController::class);
        Route::get('/ping', AttendancePingController::class);
    });
