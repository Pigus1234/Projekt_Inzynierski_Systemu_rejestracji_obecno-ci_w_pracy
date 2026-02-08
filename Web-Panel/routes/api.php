<?php

use App\Http\Controllers\Api\AttendancePingController;
use App\Http\Controllers\Api\AttendanceTapController;
use App\Http\Middleware\AuthenticateAttendanceDevice;
use Illuminate\Support\Facades\Route;

Route::post('/attendance/tap', AttendanceTapController::class)
    ->middleware([
        AuthenticateAttendanceDevice::class,
        'throttle:attendance-device',
    ]);

Route::get('/attendance/ping', AttendancePingController::class)
    ->middleware([
        AuthenticateAttendanceDevice::class,
        'throttle:attendance-device',
    ]);
