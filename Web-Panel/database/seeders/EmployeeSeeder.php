<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departmentNames = DB::table('departments')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $faker = fake('pl_PL');
        $now = now();

        $employeeCount = 30;

        $usedRfidUids = [];
        $usedCardIdentifiers = [];

        $employees = [];

        for ($index = 0; $index < $employeeCount; $index++) {
            do {
                $rfidUid = strtoupper(bin2hex(random_bytes(4)));
            } while (isset($usedRfidUids[$rfidUid]));
            $usedRfidUids[$rfidUid] = true;

            $rfidCardIdentifier = null;
            if (random_int(0, 99) < 85) {
                do {
                    $rfidCardIdentifier = 'CARD-' . strtoupper(bin2hex(random_bytes(3)));
                } while (isset($usedCardIdentifiers[$rfidCardIdentifier]));
                $usedCardIdentifiers[$rfidCardIdentifier] = true;
            }

            $fullName = trim($faker->firstName() . ' ' . $faker->lastName());

            $department = null;
            if (!empty($departmentNames) && random_int(0, 99) < 90) {
                $department = $departmentNames[array_rand($departmentNames)];
            }

            $deletedAt = null;
            if (random_int(1, 25) === 1) {
                $deletedAt = $now->copy()->subDays(random_int(1, 30));
            }

            $employees[] = [
                'rfid_uid' => $rfidUid,
                'rfid_card_identifier' => $rfidCardIdentifier,
                'full_name' => $fullName,
                'department' => $department,
                'deleted_at' => $deletedAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('employees')->insert($employees);
    }
}
