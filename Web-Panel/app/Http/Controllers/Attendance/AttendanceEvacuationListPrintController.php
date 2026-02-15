<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class AttendanceEvacuationListPrintController extends Controller
{
    public function __invoke(): View
    {
        $latestEventTimestamps = DB::table('attendance_events')
            ->select('employee_id', DB::raw('MAX(occurred_at) as last_occurred_at'))
            ->groupBy('employee_id');

        $presentEmployees = DB::table('employees')
            ->joinSub($latestEventTimestamps, 'latest_events', function ($join): void {
                $join->on('employees.id', '=', 'latest_events.employee_id');
            })
            ->join('attendance_events as last_events', function ($join): void {
                $join->on('employees.id', '=', 'last_events.employee_id')
                    ->on('latest_events.last_occurred_at', '=', 'last_events.occurred_at');
            })
            ->whereNull('employees.deleted_at')
            ->where('last_events.event_type', '=', 'entry')
            ->orderBy('employees.department')
            ->orderBy('employees.full_name')
            ->select([
                'employees.full_name',
                'employees.department',
            ])
            ->get();

        $departments = $presentEmployees
            ->groupBy(fn ($employee) => $employee->department ?? 'Bez działu')
            ->map(fn ($employeesInDepartment, $departmentName) => [
                'departmentName' => $departmentName,
                'employees' => $employeesInDepartment
                    ->map(fn ($employee) => ['fullName' => $employee->full_name])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return view('attendance.evacuation-list', [
            'departments' => $departments,
            'printedAt' => now(),
        ]);
    }
}
