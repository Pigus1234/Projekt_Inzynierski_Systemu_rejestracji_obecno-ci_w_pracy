<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
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

        Gate::before(function (User $user): ?bool {
            if ($user->role?->name === 'Administrator') {
                return true;
            }

            return null;
        });

        Gate::define('administrator.only', function (User $user): bool {
            return $user->role?->name === 'Administrator';
        });

        foreach (array_keys(config('permissions', [])) as $permissionKey) {
            Gate::define($permissionKey, function (User $user) use ($permissionKey): bool {
                return $user->permissions()
                    ->where('key', $permissionKey)
                    ->exists();
            });
        }
    }
}
