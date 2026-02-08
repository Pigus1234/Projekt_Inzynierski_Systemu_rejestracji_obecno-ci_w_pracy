<?php

namespace App\Http\Middleware;

use App\Models\AttendanceDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAttendanceDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $headerName = config('attendance.device_token_header', 'X-Attendance-Device-Token');
        $plainToken = $request->headers->get($headerName);

        if (!is_string($plainToken) || $plainToken === '') {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'device_token_missing',
                    'message' => 'Brak tokenu urządzenia.',
                ],
            ], 401);
        }

        $tokenHash = hash('sha256', $plainToken);

        $attendanceDevice = AttendanceDevice::query()
            ->where('api_token_hash', $tokenHash)
            ->where('is_active', true)
            ->first();

        if (!$attendanceDevice) {
            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'device_token_invalid',
                    'message' => 'Nieprawidłowy token urządzenia.',
                ],
            ], 401);
        }

        DB::table('attendance_devices')
            ->where('id', $attendanceDevice->id)
            ->update(['last_seen_at' => now()]);

        $request->attributes->set('attendanceDevice', $attendanceDevice);

        return $next($request);
    }
}
