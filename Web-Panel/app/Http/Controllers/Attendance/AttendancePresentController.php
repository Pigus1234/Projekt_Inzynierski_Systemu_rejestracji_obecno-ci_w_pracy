<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class AttendancePresentController extends Controller
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
                'employees.id',
                'employees.full_name',
                'employees.department',
                'employees.rfid_uid',
                'last_events.occurred_at as last_entry_at',
            ])
            ->get();

        return view('attendance.present', [
            'presentEmployees' => $presentEmployees,
        ]);
    }
}
