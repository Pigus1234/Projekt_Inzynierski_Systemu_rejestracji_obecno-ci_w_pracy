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
        Gate::define('access-administrator-panel', function (User $user): bool {
            if ($user->role?->name === 'Administrator') {
                return true;
            }

            return $user->permissions()->where('key', 'administrator.panel')->exists();
        });
    }
}
