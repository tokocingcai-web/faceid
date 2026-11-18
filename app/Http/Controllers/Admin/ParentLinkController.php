<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentLinkController extends Controller
{
    public function index(): View
    {
        return view('admin.parent-links.index', [
            'parents' => User::query()->active()->role('wali')->orderBy('name')->get(),
            'students' => User::query()->active()->role('student')->with('schoolClass')->orderBy('name')->get(),
            'links' => User::query()
                ->role('wali')
                ->with(['children.schoolClass'])
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'parent_id' => ['required', 'exists:users,id', 'different:student_id'],
            'student_id' => ['required', 'exists:users,id'],
            'relationship' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $parent = User::query()->whereKey($validated['parent_id'])->where('role', 'wali')->firstOrFail();
        $student = User::query()->whereKey($validated['student_id'])->where('role', 'student')->firstOrFail();

        if ($parent->children()->whereKey($student->id)->exists()) {
            return back()->withErrors(['student_id' => 'Relasi wali dan siswa tersebut sudah ada.']);
        }

        $parent->children()->attach($student->id, [
            'relationship' => $validated['relationship'] ?? null,
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return redirect()->route('admin.parent-links.index')->with('status', 'Relasi wali dan siswa berhasil ditambahkan.');
    }

    public function destroy(User $parent, User $student): RedirectResponse
    {
        $parent->children()->detach($student->id);

        return redirect()->route('admin.parent-links.index')->with('status', 'Relasi berhasil dihapus.');
    }
}
