<?php

return [
    'model_url' => env('FACE_MODEL_URL', 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1/model'),

    'match_threshold' => (float) env('FACE_MATCH_THRESHOLD', 0.5),

    'timezone' => env('FACE_TIMEZONE', 'Asia/Jakarta'),

    'roles' => ['guru', 'student'],
];
