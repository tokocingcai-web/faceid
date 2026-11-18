<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function redirect(): RedirectResponse
    {
        return match (auth()->user()->role) {
            'dev' => redirect()->route('dev.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            'wali' => redirect()->route('wali.dashboard'),
            default => redirect()->route('login'),
        };
    }

    public function admin(): View
    {
        $today = $this->today();
        $studentIds = User::query()->active()->role('student')->pluck('id');
        $attendances = Attendance::query()
            ->whereDate('date', $today->toDateString())
            ->whereIn('user_id', $studentIds)
            ->with(['user.schoolClass'])
            ->get();

        $counts = $this->counts($attendances);
        $notYet = max(0, $studentIds->count() - $attendances->pluck('user_id')->unique()->count());

        $classes = SchoolClass::query()
            ->withCount('students')
            ->orderBy('name')
            ->get()
            ->map(function (SchoolClass $class) use ($attendances) {
                $classAttendances = $attendances->where('class_id', $class->id);

                return [
                    'class' => $class,
                    'students' => $class->students_count,
                    'hadir' => $classAttendances->where('status', 'hadir')->count(),
                    'izin' => $classAttendances->where('status', 'izin')->count(),
                    'sakit' => $classAttendances->where('status', 'sakit')->count(),
                    'alfa' => $classAttendances->where('status', 'alfa')->count(),
                    'belum' => max(0, $class->students_count - $classAttendances->pluck('user_id')->unique()->count()),
                ];
            });

        return view('admin.dashboard', [
            'today' => $today,
            'counts' => $counts,
            'notYet' => $notYet,
            'classes' => $classes,
            'recentAttendances' => $attendances->sortByDesc('time')->take(10),
            'pendingLeaves' => LeaveRequest::query()
                ->pending()
                ->with('student')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function dev(): View
    {
        return $this->admin();
    }

    public function guru(): View
    {
        $user = auth()->user();
        $today = $this->today();

        $classes = SchoolClass::query()
            ->where('homeroom_teacher_id', $user->id)
            ->orWhereHas('schedules', fn ($query) => $query->where('teacher_id', $user->id))
            ->get();

        $classIds = $classes->pluck('id');
        $studentIds = User::query()->whereIn('class_id', $classIds)->pluck('id');

        $attendances = Attendance::query()
            ->whereDate('date', $today->toDateString())
            ->whereIn('user_id', $studentIds)
            ->with('user')
            ->get();

        $schedules = Schedule::query()
            ->with(['schoolClass', 'subject'])
            ->where('teacher_id', $user->id)
            ->where('day_of_week', $today->dayOfWeekIso)
            ->orderBy('start_time')
            ->get();

        return view('guru.dashboard', [
            'today' => $today,
            'classes' => $classes,
            'schedules' => $schedules,
            'counts' => $this->counts($attendances),
            'notYet' => max(0, $studentIds->count() - $attendances->pluck('user_id')->unique()->count()),
            'recentAttendances' => $attendances->sortByDesc('time')->take(10),
            'pendingLeaves' => LeaveRequest::query()
                ->pending()
                ->whereIn('student_id', $studentIds)
                ->with('student')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function student(): View
    {
        $user = auth()->user();

        return view('student.dashboard', [
            'todayAttendance' => $this->attendanceService->todayFor($user),
            'leaveRequests' => $user->leaveRequests()->latest()->take(5)->get(),
        ]);
    }

    public function wali(): View
    {
        $user = auth()->user();
        $today = $this->today();
        $start = $today->copy()->startOfMonth();
        $end = $today->copy()->endOfMonth();

        $summaries = $user->children()
            ->with('schoolClass')
            ->orderBy('name')
            ->get()
            ->map(function (User $student) use ($today, $start, $end) {
                $monthly = Attendance::query()
                    ->where('user_id', $student->id)
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                    ->get();

                return [
                    'student' => $student,
                    'today' => $monthly->first(fn (Attendance $attendance) => $attendance->date->isSameDay($today)),
                    'hadir' => $monthly->where('status', 'hadir')->where('late_minutes', 0)->count(),
                    'terlambat' => $monthly->where('late_minutes', '>', 0)->count(),
                    'izin' => $monthly->where('status', 'izin')->count(),
                    'sakit' => $monthly->where('status', 'sakit')->count(),
                    'alfa' => $monthly->where('status', 'alfa')->count(),
                ];
            });

        return view('wali.dashboard', [
            'summaries' => $summaries,
            'today' => $today,
            'leaveRequests' => $user->submittedLeaveRequests()->with('student')->latest()->take(5)->get(),
        ]);
    }

    private function today(): Carbon
    {
        return now()->timezone(config('face.timezone'));
    }

    /**
     * @param  Collection<int, Attendance>  $attendances
     * @return array<string, int>
     */
    private function counts(Collection $attendances): array
    {
        $hadir = $attendances->where('status', 'hadir')->count();
        $late = $attendances->where('late_minutes', '>', 0)->count();

        return [
            'hadir' => $hadir - $late,
            'terlambat' => $late,
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alfa' => $attendances->where('status', 'alfa')->count(),
        ];
    }
}
