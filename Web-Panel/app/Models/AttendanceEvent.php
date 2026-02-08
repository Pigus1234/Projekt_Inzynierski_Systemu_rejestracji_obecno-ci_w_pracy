<?php

namespace App\Models;

use App\Attendance\AttendanceEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceEvent extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_device_id',
        'recorded_by_user_id',
        'event_type',
        'occurred_at',
        'metadata',
    ];

    protected $casts = [
        'event_type' => AttendanceEventType::class,
        'occurred_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceDevice(): BelongsTo
    {
        return $this->belongsTo(AttendanceDevice::class);
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
