<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'schedule_id',
    'class_id',
    'leave_request_id',
    'date',
    'time',
    'check_out_time',
    'late_minutes',
    'early_leave_minutes',
    'status',
    'notes',
])]
class Attendance extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function isLate(): bool
    {
        return $this->status === 'hadir' && $this->late_minutes > 0;
    }

    public function displayStatus(): string
    {
        if ($this->isLate()) {
            return 'Terlambat';
        }

        return match ($this->status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alfa' => 'Alfa',
            default => ucfirst($this->status),
        };
    }
}
