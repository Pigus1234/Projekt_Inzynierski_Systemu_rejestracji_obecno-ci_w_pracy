<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = config('permissions', []);

        foreach ($permissions as $key => $label) {
            Permission::query()->updateOrCreate(
                ['key' => $key],
                ['label' => $label]
            );
        }
    }
}
