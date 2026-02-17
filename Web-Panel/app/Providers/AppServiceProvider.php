<?php

namespace App\Providers;

use App\Authorization\PermissionsGateRegistrar;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination.tailwind');
        
        $this->configureAttendanceDeviceRateLimiter();

        app(PermissionsGateRegistrar::class)->register();
    }

    private function configureAttendanceDeviceRateLimiter(): void
    {
        RateLimiter::for('attendance-device', function (Request $request) {
            $attendanceDevice = $request->attributes->get('attendanceDevice');
            $key = $attendanceDevice ? 'attendanceDevice:' . $attendanceDevice->id : 'attendanceIp:' . $request->ip();

            return Limit::perMinute((int) config('attendance.device_rate_limit_per_minute', 120))
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    return response()
                        ->json([
                            'status' => 'error',
                            'error' => [
                                'code' => 'rate_limited',
                                'message' => 'Zbyt wiele żądań. Spróbuj ponownie za chwilę.',
                            ],
                        ], 429)
                        ->withHeaders($headers);
                });
        });
    }
}