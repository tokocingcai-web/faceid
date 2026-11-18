<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function daily(Request $request): View
    {
        $data = $this->dailyData($request);

        return view('reports.daily', $data);
    }

    public function dailyExport(Request $request): StreamedResponse
    {
        $data = $this->dailyData($request);

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'NIS', 'Nama', 'Kelas', 'Status', 'Jam Masuk', 'Jam Pulang', 'Terlambat (menit)', 'Pulang Cepat (menit)']);

            foreach ($data['rows'] as $row) {
                fputcsv($handle, [
                    $data['date']->format('Y-m-d'),
                    $row['student']->nis,
                    $row['student']->name,
                    $row['student']->schoolClass?->name,
                    $row['attendance']?->displayStatus() ?? 'Belum Absen',
                    $row['attendance']?->time,
                    $row['attendance']?->check_out_time,
                    $row['attendance']?->late_minutes ?? 0,
                    $row['attendance']?->early_leave_minutes ?? 0,
                ]);
            }

            fclose($handle);
        }, 'laporan-harian-'.$data['date']->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function monthly(Request $request): View
    {
        $data = $this->monthlyData($request);

        return view('reports.monthly', $data);
    }

    public function monthlyExport(Request $request): StreamedResponse
    {
        $data = $this->monthlyData($request);

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['NIS', 'Nama', 'Kelas', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alfa', 'Persentase Kehadiran']);

            foreach ($data['rows'] as $row) {
                fputcsv($handle, [
                    $row['student']->nis,
                    $row['student']->name,
                    $row['student']->schoolClass?->name,
                    $row['hadir'],
                    $row['terlambat'],
                    $row['izin'],
                    $row['sakit'],
                    $row['alfa'],
                    $row['percentage'].'%',
                ]);
            }

            fclose($handle);
        }, 'laporan-bulanan-'.$data['month']->format('Y-m').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array<string, mixed>
     */
    private function dailyData(Request $request): array
    {
        $request->validate([
            'date' => ['nullable', 'date'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $classes = $this->classesFor($request->user());
        $date = Carbon::parse($request->input('date', now()->timezone(config('face.timezone'))->toDateString()));
        $classId = $request->input('class_id');

        $students = $this->students($classes, $classId);
        $attendances = Attendance::query()
            ->whereDate('date', $date->toDateString())
            ->whereIn('user_id', $students->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $rows = $students->map(fn (User $student) => [
            'student' => $student,
            'attendance' => $attendances->get($student->id),
        ]);

        return [
            'classes' => $classes,
            'date' => $date,
            'classId' => $classId,
            'rows' => $rows,
            'summary' => [
                'hadir' => $rows->filter(fn ($row) => $row['attendance']?->status === 'hadir' && ! $row['attendance']?->isLate())->count(),
                'terlambat' => $rows->filter(fn ($row) => $row['attendance']?->isLate())->count(),
                'izin' => $rows->filter(fn ($row) => $row['attendance']?->status === 'izin')->count(),
                'sakit' => $rows->filter(fn ($row) => $row['attendance']?->status === 'sakit')->count(),
                'alfa' => $rows->filter(fn ($row) => $row['attendance']?->status === 'alfa')->count(),
                'belum' => $rows->filter(fn ($row) => ! $row['attendance'])->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function monthlyData(Request $request): array
    {
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $classes = $this->classesFor($request->user());
        $month = Carbon::createFromFormat('Y-m', $request->input('month', now()->timezone(config('face.timezone'))->format('Y-m')))->startOfMonth();
        $classId = $request->input('class_id');

        $students = $this->students($classes, $classId);
        $attendances = Attendance::query()
            ->whereIn('user_id', $students->pluck('id'))
            ->whereBetween('date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->get()
            ->groupBy('user_id');

        $rows = $students->map(function (User $student) use ($attendances) {
            $records = $attendances->get($student->id, collect());
            $hadir = $records->where('status', 'hadir')->where('late_minutes', 0)->count();
            $terlambat = $records->where('late_minutes', '>', 0)->count();
            $total = $records->count();

            return [
                'student' => $student,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $records->where('status', 'izin')->count(),
                'sakit' => $records->where('status', 'sakit')->count(),
                'alfa' => $records->where('status', 'alfa')->count(),
                'percentage' => $total > 0 ? round((($hadir + $terlambat) / $total) * 100, 1) : 0,
            ];
        });

        return [
            'classes' => $classes,
            'month' => $month,
            'classId' => $classId,
            'rows' => $rows,
        ];
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    private function classesFor(User $user): Collection
    {
        $query = SchoolClass::query()->with('academicYear')->orderBy('name');

        if ($user->role === 'guru') {
            $query->where('homeroom_teacher_id', $user->id)
                ->orWhereHas('schedules', fn ($builder) => $builder->where('teacher_id', $user->id));
        }

        return $query->get();
    }

    /**
     * @param  Collection<int, SchoolClass>  $classes
     * @return Collection<int, User>
     */
    private function students(Collection $classes, ?string $classId): Collection
    {
        $query = User::query()
            ->active()
            ->role('student')
            ->whereIn('class_id', $classes->pluck('id'))
            ->with('schoolClass')
            ->orderBy('name');

        if ($classId) {
            $query->where('class_id', $classId);
        }

        return $query->get();
    }
}
