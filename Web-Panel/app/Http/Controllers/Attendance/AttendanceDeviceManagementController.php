<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDevice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceDeviceManagementController extends Controller
{
    public function index(): View
    {
        $attendanceDevices = AttendanceDevice::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $onlineCutoff = now()->subMinutes(5);

        foreach ($attendanceDevices as $attendanceDevice) {
            $isOnline = (bool) ($attendanceDevice->last_seen_at?->gte($onlineCutoff));
            $attendanceDevice->setAttribute('is_online', $isOnline);
        }

        return view('administrator.attendance-devices.index', [
            'attendanceDevices' => $attendanceDevices,
        ]);
    }

    public function create(): View
    {
        return view('administrator.attendance-devices.create');
    }

    public function store(Request $request): View
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('attendance_devices', 'name'),
            ],
            'isActive' => ['nullable', 'boolean'],
        ]);

        $plainToken = bin2hex(random_bytes(32));

        $attendanceDevice = AttendanceDevice::query()->create([
            'name' => $validated['name'],
            'api_token_hash' => hash('sha256', $plainToken),
            'is_active' => (bool) ($validated['isActive'] ?? true),
        ]);

        return view('administrator.attendance-devices.token', [
            'attendanceDevice' => $attendanceDevice,
            'plainToken' => $plainToken,
        ]);
    }

    public function edit(AttendanceDevice $attendanceDevice): View
    {
        return view('administrator.attendance-devices.edit', [
            'attendanceDevice' => $attendanceDevice,
        ]);
    }

    public function update(Request $request, AttendanceDevice $attendanceDevice): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('attendance_devices', 'name')->ignore($attendanceDevice->id),
            ],
        ]);

        $attendanceDevice->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('administrator.attendance-devices.index');
    }

    public function rotateToken(AttendanceDevice $attendanceDevice): View
    {
        $plainToken = bin2hex(random_bytes(32));

        $attendanceDevice->update([
            'api_token_hash' => hash('sha256', $plainToken),
        ]);

        return view('administrator.attendance-devices.token', [
            'attendanceDevice' => $attendanceDevice,
            'plainToken' => $plainToken,
        ]);
    }

    public function activate(AttendanceDevice $attendanceDevice): RedirectResponse
    {
        $attendanceDevice->update(['is_active' => true]);

        return redirect()->route('administrator.attendance-devices.index');
    }

    public function deactivate(AttendanceDevice $attendanceDevice): RedirectResponse
    {
        $attendanceDevice->update(['is_active' => false]);

        return redirect()->route('administrator.attendance-devices.index');
    }
}
