<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departmentNames = [
            'Administracja',
            'Magazyn',
            'Produkcja',
            'Utrzymanie Ruchu',
            'Jakość',
            'Logistyka',
        ];

        $now = now();

        $records = array_map(
            fn (string $name): array => [
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $departmentNames
        );

        DB::table('departments')->upsert($records, ['name'], ['updated_at']);
    }
}
