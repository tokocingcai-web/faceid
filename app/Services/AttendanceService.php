<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

class AttendanceService
{
    public function __construct(private ParentNotifier $notifier) {}

    public function todayFor(User $user): ?Attendance
    {
        $today = now()->timezone(config('face.timezone'))->toDateString();

        return Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
    }

    /**
     * @return array{status: string, message: string, attendance: Attendance|null}
     */
    public function checkIn(User $user): array
    {
        $now = now()->timezone(config('face.timezone'));
        $existing = $this->todayFor($user);

        if ($existing) {
            if (in_array($existing->status, ['izin', 'sakit'], true)) {
                return [
                    'status' => 'warning',
                    'message' => "Anda tercatat {$existing->status} hari ini. Absensi masuk tidak dibuat.",
                    'attendance' => $existing,
                ];
            }

            return [
                'status' => 'warning',
                'message' => "Absensi masuk hari ini sudah tercatat pada pukul {$existing->time}.",
                'attendance' => $existing,
            ];
        }

        $schedule = $this->activeSchedule($user, $now);
        $expectedStart = $now->copy()->setTimeFromTimeString(config('attendance.start_time'));
        $graceMinutes = (int) config('attendance.late_grace_minutes');
        $lateMinutes = 0;

        if ($now->greaterThan($expectedStart->copy()->addMinutes($graceMinutes))) {
            $lateMinutes = (int) $expectedStart->diffInMinutes($now);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule?->id,
            'class_id' => $user->class_id ?: $schedule?->class_id,
            'date' => $now->toDateString(),
            'time' => $now->toTimeString(),
            'status' => 'hadir',
            'late_minutes' => $lateMinutes,
        ]);

        if ($lateMinutes > 0) {
            $this->notifier->notifyLate($attendance);
        }

        return [
            'status' => 'success',
            'message' => $lateMinutes > 0
                ? "Absensi masuk tercatat pukul {$now->format('H:i')}. Anda terlambat {$lateMinutes} menit."
                : "Absensi masuk berhasil pukul {$now->format('H:i')}.",
            'attendance' => $attendance,
        ];
    }

    /**
     * @return array{status: string, message: string, attendance: Attendance|null}
     */
    public function checkOut(User $user): array
    {
        $now = now()->timezone(config('face.timezone'));
        $attendance = $this->todayFor($user);

        if (! $attendance || ! in_array($attendance->status, ['hadir'], true)) {
            return [
                'status' => 'error',
                'message' => 'Anda belum melakukan absensi masuk hari ini.',
                'attendance' => $attendance,
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'status' => 'warning',
                'message' => "Absensi pulang hari ini sudah tercatat pukul {$attendance->check_out_time}.",
                'attendance' => $attendance,
            ];
        }

        $expectedEnd = $now->copy()->setTimeFromTimeString(config('attendance.end_time'));
        $earlyLeaveMinutes = $now->lessThan($expectedEnd)
            ? (int) $now->diffInMinutes($expectedEnd)
            : 0;

        $attendance->update([
            'check_out_time' => $now->toTimeString(),
            'early_leave_minutes' => $earlyLeaveMinutes,
        ]);

        return [
            'status' => 'success',
            'message' => $earlyLeaveMinutes > 0
                ? "Absensi pulang tercatat pukul {$now->format('H:i')}. Anda pulang {$earlyLeaveMinutes} menit lebih awal."
                : "Absensi pulang berhasil pukul {$now->format('H:i')}.",
            'attendance' => $attendance->refresh(),
        ];
    }

    /**
     * @param  array{status: string, time?: string|null, check_out_time?: string|null, notes?: string|null}  $data
     */
    public function saveManual(User $student, Carbon $date, array $data): Attendance
    {
        $attendance = Attendance::firstOrNew([
            'user_id' => $student->id,
            'date' => $date->toDateString(),
        ]);

        $attendance->class_id = $student->class_id;

        $status = $data['status'];
        $time = $status === 'hadir' ? ($data['time'] ?? null) : null;
        $checkOutTime = $status === 'hadir' ? ($data['check_out_time'] ?? null) : null;

        $attendance->status = $status;
        $attendance->time = $time ?: '00:00:00';
        $attendance->check_out_time = $checkOutTime ?: null;
        $attendance->notes = $data['notes'] ?? null;
        $attendance->late_minutes = 0;
        $attendance->early_leave_minutes = 0;

        if ($time) {
            $expectedStart = $date->copy()->setTimeFromTimeString(config('attendance.start_time'));
            $actualStart = $date->copy()->setTimeFromTimeString($time);

            if ($actualStart->greaterThan($expectedStart->copy()->addMinutes((int) config('attendance.late_grace_minutes')))) {
                $attendance->late_minutes = (int) $expectedStart->diffInMinutes($actualStart);
            }
        }

        if ($checkOutTime) {
            $expectedEnd = $date->copy()->setTimeFromTimeString(config('attendance.end_time'));
            $actualEnd = $date->copy()->setTimeFromTimeString($checkOutTime);

            if ($actualEnd->lessThan($expectedEnd)) {
                $attendance->early_leave_minutes = (int) $actualEnd->diffInMinutes($expectedEnd);
            }
        }

        $attendance->save();

        return $attendance;
    }

    public function applyLeave(LeaveRequest $leaveRequest): int
    {
        $created = 0;
        $date = $leaveRequest->start_date->copy();
        $student = $leaveRequest->student;

        while ($date->lessThanOrEqualTo($leaveRequest->end_date)) {
            if ($this->isSchoolDay($date)) {
                $attendance = Attendance::firstOrNew([
                    'user_id' => $leaveRequest->student_id,
                    'date' => $date->toDateString(),
                ]);

                $attendance->class_id = $student->class_id;
                $attendance->leave_request_id = $leaveRequest->id;
                $attendance->status = $leaveRequest->type;
                $attendance->time = $attendance->time ?: '00:00:00';
                $attendance->late_minutes = 0;
                $attendance->early_leave_minutes = 0;
                $attendance->notes = 'Pengajuan '.$leaveRequest->typeLabel().' disetujui';
                $attendance->save();
                $created++;
            }

            $date->addDay();
        }

        return $created;
    }

    public function markAbsent(Carbon $date): int
    {
        if (! $this->isSchoolDay($date)) {
            return 0;
        }

        $students = User::query()
            ->active()
            ->role('student')
            ->whereNotNull('class_id')
            ->get();

        $presentIds = Attendance::query()
            ->whereDate('date', $date->toDateString())
            ->pluck('user_id')
            ->all();

        $leaveIds = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->pluck('student_id')
            ->all();

        $marked = 0;

        foreach ($students as $student) {
            if (in_array($student->id, $presentIds, true) || in_array($student->id, $leaveIds, true)) {
                continue;
            }

            $attendance = Attendance::create([
                'user_id' => $student->id,
                'class_id' => $student->class_id,
                'date' => $date->toDateString(),
                'time' => '00:00:00',
                'status' => 'alfa',
            ]);

            $this->notifier->notifyAbsent($attendance);
            $marked++;
        }

        return $marked;
    }

    public function isSchoolDay(Carbon $date): bool
    {
        return in_array($date->dayOfWeekIso, config('attendance.school_days'), true);
    }

    public function activeSchedule(User $user, Carbon $now): ?Schedule
    {
        $query = Schedule::query()
            ->where('day_of_week', $now->dayOfWeekIso)
            ->where('start_time', '<=', $now->format('H:i:s'))
            ->where('end_time', '>=', $now->format('H:i:s'));

        if ($user->role === 'student' && $user->class_id) {
            $query->where('class_id', $user->class_id);
        } elseif ($user->role === 'guru') {
            $query->where('teacher_id', $user->id);
        } else {
            return null;
        }

        return $query->orderBy('start_time')->first();
    }

    public function latestScheduleForToday(User $user, Carbon $now): ?Schedule
    {
        $query = Schedule::query()->where('day_of_week', $now->dayOfWeekIso);

        if ($user->role === 'student' && $user->class_id) {
            $query->where('class_id', $user->class_id);
        } elseif ($user->role === 'guru') {
            $query->where('teacher_id', $user->id);
        } else {
            return null;
        }

        return $query->orderByDesc('end_time')->first();
    }
}
