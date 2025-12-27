<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Administrator', 'Standard'] as $roleName) {
            Role::updateOrCreate(['name' => $roleName]);
        }
    }
}
