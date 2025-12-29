<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdministratorPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionIds = Permission::query()->pluck('id')->all();

        User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'Administrator'))
            ->each(fn (User $user) => $user->permissions()->sync($permissionIds));
    }
}
