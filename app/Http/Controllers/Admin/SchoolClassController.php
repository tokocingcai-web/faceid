<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        return view('admin.classes.index', [
            'classes' => SchoolClass::query()
                ->with(['academicYear', 'homeroomTeacher'])
                ->withCount(['students', 'schedules'])
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.classes.form', $this->formData(new SchoolClass));
    }

    public function store(Request $request): RedirectResponse
    {
        SchoolClass::create($request->validate($this->rules()));

        return redirect()->route('admin.classes.index')->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $class): View
    {
        return view('admin.classes.form', $this->formData($class));
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $class->update($request->validate($this->rules($class)));

        return redirect()->route('admin.classes.index')->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        abort_if($class->students()->exists(), 403, 'Kelas masih memiliki siswa.');

        $class->delete();

        return redirect()->route('admin.classes.index')->with('status', 'Kelas berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?SchoolClass $class = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('classes', 'name')
                    ->where('academic_year_id', request('academic_year_id'))
                    ->ignore($class?->id),
            ],
            'level' => ['nullable', 'string', 'max:20'],
            'major' => ['nullable', 'string', 'max:100'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'homeroom_teacher_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', 'guru'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(SchoolClass $class): array
    {
        return [
            'class' => $class,
            'academicYears' => AcademicYear::query()->orderByDesc('start_date')->get(),
            'teachers' => User::query()->active()->role('guru')->orderBy('name')->get(),
        ];
    }
}
