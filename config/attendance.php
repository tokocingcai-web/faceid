<?php

return [
    'start_time' => env('ATTENDANCE_START_TIME', '07:00'),
    'end_time' => env('ATTENDANCE_END_TIME', '15:00'),
    'late_grace_minutes' => (int) env('ATTENDANCE_LATE_GRACE_MINUTES', 15),
    'school_days' => [1, 2, 3, 4, 5],
    'whatsapp' => [
        'endpoint' => env('WHATSAPP_API_URL'),
        'token' => env('WHATSAPP_API_TOKEN'),
    ],
];
