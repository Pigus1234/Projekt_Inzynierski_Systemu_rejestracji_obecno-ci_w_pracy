<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Gate::before(function (User $user): ?bool {
            if ($user->role?->name === 'Administrator') {
                return true;
            }

            return null;
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
