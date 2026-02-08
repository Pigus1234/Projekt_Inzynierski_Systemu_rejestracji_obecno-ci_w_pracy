<?php

namespace App\Http\Controllers\Attendance;

use App\Attendance\AttendanceEventType;
use App\Http\Controllers\Controller;
use App\Models\AttendanceEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AttendanceChangelogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'employee' => ['nullable', 'string', 'max:200'],
            'department' => ['nullable', 'string', 'max:200'],
            'eventType' => ['nullable', Rule::enum(AttendanceEventType::class)],
            'dateFrom' => ['nullable', 'date_format:Y-m-d'],
            'dateTo' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $query = AttendanceEvent::query()
            ->with('employee')
            ->orderByDesc('occurred_at');

        if (!empty($validated['employee'])) {
            $employeeSearch = $validated['employee'];

            $query->whereHas('employee', function ($employeeQuery) use ($employeeSearch): void {
                $employeeQuery->where('full_name', 'like', '%' . $employeeSearch . '%');
            });
        }

        if (!empty($validated['department'])) {
            $department = $validated['department'];

            $query->whereHas('employee', function ($employeeQuery) use ($department): void {
                $employeeQuery->where('department', 'like', '%' . $department . '%');
            });
        }

        if (!empty($validated['eventType'])) {
            $query->where('event_type', '=', $validated['eventType']);
        }

        if (!empty($validated['dateFrom'])) {
            $query->where('occurred_at', '>=', Carbon::parse($validated['dateFrom'])->startOfDay());
        }

        if (!empty($validated['dateTo'])) {
            $query->where('occurred_at', '<=', Carbon::parse($validated['dateTo'])->endOfDay());
        }

        $attendanceEvents = $query
            ->paginate(50)
            ->withQueryString();

        return view('attendance.changelog', [
            'attendanceEvents' => $attendanceEvents,
            'filters' => [
                'employee' => $validated['employee'] ?? null,
                'department' => $validated['department'] ?? null,
                'eventType' => $validated['eventType'] ?? null,
                'dateFrom' => $validated['dateFrom'] ?? null,
                'dateTo' => $validated['dateTo'] ?? null,
            ],
        ]);
    }
}
