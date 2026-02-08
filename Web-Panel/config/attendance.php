<?php

return [
    'device_token_header' => env('ATTENDANCE_DEVICE_TOKEN_HEADER', 'X-Attendance-Device-Token'),
    'duplicate_event_lock_seconds' => (int) env('ATTENDANCE_DUPLICATE_EVENT_LOCK_SECONDS', 8),
];
