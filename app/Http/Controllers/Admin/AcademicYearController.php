<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        return view('admin.academic-years.index', [
            'academicYears' => AcademicYear::query()->withCount('classes')->latest('start_date')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.academic-years.form', [
            'academicYear' => new AcademicYear,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateYear($request);
        $this->saveYear(new AcademicYear, $validated);

        return redirect()->route('admin.academic-years.index')->with('status', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        return view('admin.academic-years.form', [
            'academicYear' => $academicYear,
        ]);
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $this->validateYear($request, $academicYear);
        $this->saveYear($academicYear, $validated);

        return redirect()->route('admin.academic-years.index')->with('status', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        abort_if($academicYear->classes()->exists(), 403, 'Tahun ajaran masih memiliki kelas.');

        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')->with('status', 'Tahun ajaran berhasil dihapus.');
    }

    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        DB::transaction(function () use ($academicYear) {
            AcademicYear::query()->update(['is_active' => false]);
            $academicYear->update(['is_active' => true]);
        });

        return redirect()->route('admin.academic-years.index')->with('status', 'Tahun ajaran aktif berhasil diubah.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateYear(Request $request, ?AcademicYear $academicYear = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('academic_years', 'name')->ignore($academicYear?->id)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function saveYear(AcademicYear $academicYear, array $validated): void
    {
        DB::transaction(function () use ($academicYear, $validated) {
            if (! empty($validated['is_active'])) {
                AcademicYear::query()->whereKeyNot($academicYear->id)->update(['is_active' => false]);
            }

            $academicYear->fill([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => ! empty($validated['is_active']),
            ])->save();
        });
    }
}
