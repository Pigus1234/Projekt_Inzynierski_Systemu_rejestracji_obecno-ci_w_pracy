<?php

namespace App\Http\Controllers;

use App\Attendance\AttendanceEventType;
use App\Models\AttendanceDevice;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $databaseOk = $this->checkDatabaseConnection();

        $activeDevicesCount = null;
        $onlineDevicesCount = null;
        $presentEmployeesCount = null;

        if ($databaseOk) {
            $activeDevicesCount = AttendanceDevice::query()
                ->where('is_active', true)
                ->count();

            $onlineDevicesCount = AttendanceDevice::query()
                ->where('is_active', true)
                ->where('last_seen_at', '>=', now()->subMinutes(5))
                ->count();

            $presentEmployeesCount = $this->countPresentEmployees();
        }

        $databaseStatusValue = $databaseOk ? 'OK' : 'Błąd połączenia';
        $databaseStatusState = $databaseOk ? 'ok' : 'error';

        $rfidStatusValue = is_int($onlineDevicesCount) && is_int($activeDevicesCount)
            ? ($onlineDevicesCount . ' / ' . $activeDevicesCount)
            : '—';

        $rfidStatusState = is_int($onlineDevicesCount) && $onlineDevicesCount > 0 ? 'ok' : 'warning';

        return view('dashboard', [
            'databaseStatusValue' => $databaseStatusValue,
            'databaseStatusState' => $databaseStatusState,
            'rfidStatusValue' => $rfidStatusValue,
            'rfidStatusState' => $rfidStatusState,
            'presentEmployeesCount' => $presentEmployeesCount,
        ]);
    }

    private function countPresentEmployees(): int
    {
        $lastEventPerEmployeeSubquery = DB::table('attendance_events')
            ->select('employee_id', DB::raw('MAX(id) as last_event_id'))
            ->whereNotNull('employee_id')
            ->whereIn('event_type', [
                AttendanceEventType::Entry->value,
                AttendanceEventType::Exit->value,
            ])
            ->groupBy('employee_id');

        return (int) DB::table('attendance_events as attendance_events_latest')
            ->joinSub($lastEventPerEmployeeSubquery, 'last_event_per_employee', function ($join) {
                $join->on('attendance_events_latest.id', '=', 'last_event_per_employee.last_event_id');
            })
            ->where('attendance_events_latest.event_type', AttendanceEventType::Entry->value)
            ->count();
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
