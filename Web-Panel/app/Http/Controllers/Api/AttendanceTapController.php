<?php

namespace App\Http\Controllers\Api;

use App\Attendance\AttendanceEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceTapRequest;
use App\Models\AttendanceEvent;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceTapController extends Controller
{
    public function __invoke(StoreAttendanceTapRequest $request): JsonResponse
    {
        $cardIdentifier = $this->normalizeCardIdentifier((string) $request->validated('cardIdentifier'));
        $attendanceDevice = $request->attributes->get('attendanceDevice');

        $duplicateLockSeconds = (int) config('attendance.duplicate_event_lock_seconds', 8);

        $employee = Employee::query()
            ->where('rfid_uid', $cardIdentifier)
            ->first();

        if (!$employee) {
            $occurredAt = Carbon::now();

            $lastUnknownAttempt = AttendanceEvent::query()
                ->whereNull('employee_id')
                ->where('event_type', AttendanceEventType::UnknownCardAttempt)
                ->where('metadata->rfidUid', $cardIdentifier)
                ->orderByDesc('occurred_at')
                ->first();

            $shouldCreateEvent = true;

            if ($lastUnknownAttempt) {
                $secondsSinceLastEvent = abs($occurredAt->diffInSeconds($lastUnknownAttempt->occurred_at));
                if ($secondsSinceLastEvent < $duplicateLockSeconds) {
                    $shouldCreateEvent = false;
                }
            }

            if ($shouldCreateEvent) {
                AttendanceEvent::query()->create([
                    'employee_id' => null,
                    'attendance_device_id' => $attendanceDevice?->id,
                    'recorded_by_user_id' => null,
                    'event_type' => AttendanceEventType::UnknownCardAttempt,
                    'occurred_at' => $occurredAt,
                    'metadata' => [
                        'rfidUid' => $cardIdentifier,
                        'errorCode' => 'employee_not_found',
                    ],
                ]);
            }

            return response()->json([
                'status' => 'error',
                'error' => [
                    'code' => 'employee_not_found',
                    'message' => 'Nie znaleziono pracownika dla podanego identyfikatora karty.',
                ],
            ], 404);
        }

        [$attendanceEvent, $createdNewEvent] = DB::transaction(function () use ($employee, $attendanceDevice, $duplicateLockSeconds, $cardIdentifier): array {
            $occurredAt = Carbon::now();

            $lastEvent = AttendanceEvent::query()
                ->where('employee_id', $employee->id)
                ->orderByDesc('occurred_at')
                ->lockForUpdate()
                ->first();

            if ($lastEvent) {
                $secondsSinceLastEvent = abs($occurredAt->diffInSeconds($lastEvent->occurred_at));
                if ($secondsSinceLastEvent < $duplicateLockSeconds) {
                    return [$lastEvent, false];
                }
            }

            $eventType = $lastEvent && $lastEvent->event_type === AttendanceEventType::Entry
                ? AttendanceEventType::Exit
                : AttendanceEventType::Entry;

            $attendanceEvent = AttendanceEvent::query()->create([
                'employee_id' => $employee->id,
                'attendance_device_id' => $attendanceDevice?->id,
                'recorded_by_user_id' => null,
                'event_type' => $eventType,
                'occurred_at' => $occurredAt,
                'metadata' => [
                    'rfidUid' => $cardIdentifier,
                ],
            ]);

            return [$attendanceEvent, true];
        });

        $presenceStatus = $attendanceEvent->event_type === AttendanceEventType::Entry ? 'present' : 'absent';

        return response()->json([
            'status' => 'ok',
            'createdNewEvent' => $createdNewEvent,
            'employee' => [
                'id' => $employee->id,
            ],
            'event' => [
                'id' => $attendanceEvent->id,
                'type' => $attendanceEvent->event_type->value,
                'occurredAt' => $attendanceEvent->occurred_at->toIso8601String(),
            ],
            'presenceStatus' => $presenceStatus,
        ]);
    }

    private function normalizeCardIdentifier(string $cardIdentifier): string
    {
        $withoutSeparators = preg_replace('/[^a-fA-F0-9]/', '', $cardIdentifier);
        $withoutSeparators = is_string($withoutSeparators) && $withoutSeparators !== '' ? $withoutSeparators : $cardIdentifier;

        return strtoupper($withoutSeparators);
    }
}
