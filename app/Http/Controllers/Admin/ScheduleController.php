<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Schedule::query()->with(['academicYear', 'schoolClass', 'subject', 'teacher']);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->input('academic_year_id'));
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->input('teacher_id'));
        }

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->input('day_of_week'));
        }

        return view('admin.schedules.index', [
            'schedules' => $query->orderBy('day_of_week')->orderBy('start_time')->paginate(30)->withQueryString(),
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'classes' => SchoolClass::query()->orderBy('name')->get(),
            'teachers' => User::query()->active()->role('guru')->orderBy('name')->get(),
            'days' => Schedule::DAYS,
        ]);
    }

    public function create(): View
    {
        return view('admin.schedules.form', $this->formData(new Schedule));
    }

    public function store(Request $request): RedirectResponse
    {
        Schedule::create($request->validate($this->rules()));

        return redirect()->route('admin.schedules.index')->with('status', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Schedule $schedule): View
    {
        return view('admin.schedules.form', $this->formData($schedule));
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $schedule->update($request->validate($this->rules()));

        return redirect()->route('admin.schedules.index')->with('status', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('admin.schedules.index')->with('status', 'Jadwal berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', Rule::exists('users', 'id')->where('role', 'guru')],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Schedule $schedule): array
    {
        return [
            'schedule' => $schedule,
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'classes' => SchoolClass::query()->with('academicYear')->orderBy('name')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'teachers' => User::query()->active()->role('guru')->orderBy('name')->get(),
            'days' => Schedule::DAYS,
        ];
    }
}
