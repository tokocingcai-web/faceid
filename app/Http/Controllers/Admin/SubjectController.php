<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        return view('admin.subjects.index', [
            'subjects' => Subject::query()->withCount('schedules')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.subjects.form', [
            'subject' => new Subject,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Subject::create($request->validate($this->rules()));

        return redirect()->route('admin.subjects.index')->with('status', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.form', [
            'subject' => $subject,
        ]);
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validate($this->rules($subject)));

        return redirect()->route('admin.subjects.index')->with('status', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        abort_if($subject->schedules()->exists(), 403, 'Mata pelajaran masih digunakan pada jadwal.');

        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('status', 'Mata pelajaran berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Subject $subject = null): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('subjects', 'code')->ignore($subject?->id)],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
