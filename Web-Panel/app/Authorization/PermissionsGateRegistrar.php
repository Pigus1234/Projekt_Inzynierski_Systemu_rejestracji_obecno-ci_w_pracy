<?php

namespace App\Authorization;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class PermissionsGateRegistrar
{
    public function register(): void
    {
        Gate::before(function (User $user): ?bool {
            return $user->role?->name === 'Administrator' ? true : null;
        });

        Gate::define('administrator.only', fn (User $user): bool => $user->role?->name === 'Administrator');

        $permissionKeys = array_keys((array) config('permissions', []));

        foreach ($permissionKeys as $permissionKey) {
            Gate::define($permissionKey, function (User $user) use ($permissionKey): bool {
                return $user->permissions()
                    ->where('key', $permissionKey)
                    ->exists();
            });
        }
    }
}
