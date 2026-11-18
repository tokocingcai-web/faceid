<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function index(Request $request): View
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'status' => ['nullable', 'in:hadir,terlambat,izin,sakit,alfa,belum'],
        ]);

        $actor = $request->user();
        $classes = $this->classesFor($actor);
        $classId = $request->input('class_id');
        $date = Carbon::parse(
            $request->input('date', now()->timezone(config('face.timezone'))->toDateString()),
            config('face.timezone')
        )->startOfDay();

        $studentsQuery = User::query()
            ->active()
            ->role('student')
            ->with('schoolClass')
            ->orderBy('name');

        if ($actor->role === 'guru') {
            $studentsQuery->whereIn('class_id', $classes->pluck('id'));
        }

        if ($classId) {
            $studentsQuery->where('class_id', $classId);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $studentsQuery->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $status = $request->input('status');

        if ($status === 'belum') {
            $studentsQuery->whereDoesntHave('attendances', fn ($query) => $query->whereDate('date', $date));
        } elseif (in_array($status, ['hadir', 'terlambat'], true)) {
            $studentsQuery->whereHas('attendances', function ($query) use ($date, $status) {
                $query->whereDate('date', $date)->where('status', 'hadir');

                if ($status === 'terlambat') {
                    $query->where('late_minutes', '>', 0);
                } else {
                    $query->where('late_minutes', 0);
                }
            });
        } elseif ($status) {
            $studentsQuery->whereHas(
                'attendances',
                fn ($query) => $query->whereDate('date', $date)->where('status', $status)
            );
        }

        $students = $studentsQuery->paginate(25)->withQueryString();

        $attendances = Attendance::query()
            ->whereDate('date', $date)
            ->whereIn('user_id', $students->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $summaryStudents = User::query()
            ->active()
            ->role('student')
            ->when($actor->role === 'guru', fn ($query) => $query->whereIn('class_id', $classes->pluck('id')))
            ->when($classId, fn ($query) => $query->where('class_id', $classId))
            ->pluck('id');

        $summaryAttendances = Attendance::query()
            ->whereDate('date', $date)
            ->whereIn('user_id', $summaryStudents)
            ->get();

        $late = $summaryAttendances->where('late_minutes', '>', 0)->count();

        return view('attendance.manage.index', [
            'students' => $students,
            'attendances' => $attendances,
            'classes' => $classes,
            'classId' => $classId,
            'date' => $date,
            'summary' => [
                'hadir' => $summaryAttendances->where('status', 'hadir')->count() - $late,
                'terlambat' => $late,
                'izin' => $summaryAttendances->where('status', 'izin')->count(),
                'sakit' => $summaryAttendances->where('status', 'sakit')->count(),
                'alfa' => $summaryAttendances->where('status', 'alfa')->count(),
                'belum' => max(0, $summaryStudents->count() - $summaryAttendances->pluck('user_id')->unique()->count()),
            ],
        ]);
    }

    public function edit(Request $request, User $student): View
    {
        $this->authorizeStudent($request->user(), $student);

        $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = Carbon::parse(
            $request->input('date', now()->timezone(config('face.timezone'))->toDateString()),
            config('face.timezone')
        )->startOfDay();

        $attendance = Attendance::query()
            ->where('user_id', $student->id)
            ->whereDate('date', $date)
            ->first();

        return view('attendance.manage.edit', [
            'student' => $student->load('schoolClass'),
            'attendance' => $attendance,
            'date' => $date,
        ]);
    }

    public function update(Request $request, User $student): RedirectResponse
    {
        $this->authorizeStudent($request->user(), $student);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'status' => ['required', 'in:hadir,izin,sakit,alfa'],
            'time' => ['required_if:status,hadir', 'nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $date = Carbon::parse($validated['date'], config('face.timezone'))->startOfDay();

        $this->attendanceService->saveManual($student, $date, $validated);

        return redirect()
            ->route('attendance-manage.index', [
                'date' => $date->toDateString(),
                'class_id' => $student->class_id,
            ])
            ->with('status', 'Absensi '.$student->name.' berhasil disimpan.');
    }

    public function destroy(Request $request, User $student, string $date): RedirectResponse
    {
        $this->authorizeStudent($request->user(), $student);

        $parsed = Carbon::parse($date, config('face.timezone'))->startOfDay();

        Attendance::query()
            ->where('user_id', $student->id)
            ->whereDate('date', $parsed)
            ->delete();

        return redirect()
            ->route('attendance-manage.index', [
                'date' => $parsed->toDateString(),
                'class_id' => $student->class_id,
            ])
            ->with('status', 'Catatan absensi '.$student->name.' berhasil direset.');
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    private function classesFor(User $user): Collection
    {
        $query = SchoolClass::query()->orderBy('name');

        if ($user->role === 'guru') {
            $query->where('homeroom_teacher_id', $user->id)
                ->orWhereHas('schedules', fn ($builder) => $builder->where('teacher_id', $user->id));
        }

        return $query->get();
    }

    private function authorizeStudent(User $actor, User $student): void
    {
        abort_unless($student->role === 'student', 404);

        if (in_array($actor->role, ['admin', 'dev'], true)) {
            return;
        }

        abort_unless(
            $student->class_id && $this->classesFor($actor)->pluck('id')->contains($student->class_id),
            403
        );
    }
}
