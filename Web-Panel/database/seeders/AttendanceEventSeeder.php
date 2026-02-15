<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceEventSeeder extends Seeder
{
    public function run(): void
    {
        $attendanceDeviceId = DB::table('attendance_devices')->value('id');

        $employees = DB::table('employees')
            ->whereNull('deleted_at')
            ->select(['id'])
            ->get();

        $now = now();
        $events = [];

        foreach ($employees as $employee) {
            $shouldBePresentNow = random_int(0, 99) < 40;
            $historyDays = random_int(1, 4);

            for ($day = $historyDays; $day >= 1; $day--) {
                $date = Carbon::now()->subDays($day);

                $entryAt = $date->copy()->setTime(random_int(6, 9), random_int(0, 59), random_int(0, 59));
                $exitAt = $date->copy()->setTime(random_int(14, 18), random_int(0, 59), random_int(0, 59));

                if ($exitAt->lessThanOrEqualTo($entryAt)) {
                    $exitAt = $entryAt->copy()->addHours(random_int(6, 10));
                }

                $events[] = [
                    'employee_id' => $employee->id,
                    'attendance_device_id' => $attendanceDeviceId,
                    'recorded_by_user_id' => null,
                    'event_type' => 'entry',
                    'occurred_at' => $entryAt,
                    'metadata' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $events[] = [
                    'employee_id' => $employee->id,
                    'attendance_device_id' => $attendanceDeviceId,
                    'recorded_by_user_id' => null,
                    'event_type' => 'exit',
                    'occurred_at' => $exitAt,
                    'metadata' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $entryToday = Carbon::now()->subMinutes(random_int(5, 240));

            $events[] = [
                'employee_id' => $employee->id,
                'attendance_device_id' => $attendanceDeviceId,
                'recorded_by_user_id' => null,
                'event_type' => 'entry',
                'occurred_at' => $entryToday,
                'metadata' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (!$shouldBePresentNow) {
                $exitToday = $entryToday->copy()->addMinutes(random_int(60, 600));

                $events[] = [
                    'employee_id' => $employee->id,
                    'attendance_device_id' => $attendanceDeviceId,
                    'recorded_by_user_id' => null,
                    'event_type' => 'exit',
                    'occurred_at' => $exitToday,
                    'metadata' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $unknownCount = 12;
        for ($index = 0; $index < $unknownCount; $index++) {
            $events[] = [
                'employee_id' => null,
                'attendance_device_id' => $attendanceDeviceId,
                'recorded_by_user_id' => null,
                'event_type' => 'unknown_card_attempt',
                'occurred_at' => Carbon::now()->subMinutes(random_int(10, 20000)),
                'metadata' => json_encode(['rfid_uid' => strtoupper(bin2hex(random_bytes(4)))], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($events, 1000) as $chunk) {
            DB::table('attendance_events')->insert($chunk);
        }
    }
}
