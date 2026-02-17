<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AttendanceDeviceSeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            AttendanceEventSeeder::class,
        ]);
    }
}
