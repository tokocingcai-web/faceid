<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\ParentNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private ParentNotifier $notifier
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = LeaveRequest::query()->with(['student.schoolClass', 'requester', 'reviewer']);

        if ($user->role === 'student') {
            $query->where('student_id', $user->id);
        } elseif ($user->role === 'wali') {
            $query->whereIn('student_id', $user->children()->pluck('users.id'));
        } elseif ($user->role === 'guru') {
            $query->whereIn('student_id', $this->studentIdsForTeacher($user));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        return view('leave-requests.index', [
            'leaveRequests' => $query->latest()->paginate(20)->withQueryString(),
            'canCreate' => in_array($user->role, ['student', 'wali'], true),
            'canReview' => in_array($user->role, ['admin', 'dev', 'guru'], true),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['student', 'wali'], true), 403);

        $students = $user->role === 'wali'
            ? $user->children()->role('student')->orderBy('name')->get()
            : collect([$user]);

        return view('leave-requests.create', [
            'students' => $students,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['student', 'wali'], true), 403);

        $validated = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'type' => ['required', 'in:izin,sakit'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        $studentId = $user->role === 'student' ? $user->id : (int) ($validated['student_id'] ?? 0);

        if ($user->role === 'wali') {
            abort_unless($user->children()->whereKey($studentId)->exists(), 403);
        }

        $attachment = $request->file('attachment')?->store('leave-attachments', 'public');

        LeaveRequest::create([
            'student_id' => $studentId,
            'requested_by' => $user->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'attachment' => $attachment,
        ]);

        return redirect()
            ->route('leave-requests.index')
            ->with('status', 'Pengajuan izin/sakit berhasil dikirim dan menunggu persetujuan.');
    }

    public function destroy(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $user = $request->user();
        $allowed = $user->role === 'student'
            ? $leaveRequest->student_id === $user->id
            : ($user->role === 'wali'
                ? $leaveRequest->requested_by === $user->id
                : in_array($user->role, ['admin', 'dev'], true));

        abort_unless($allowed, 403);
        abort_unless($leaveRequest->status === 'pending', 403, 'Hanya pengajuan yang masih menunggu yang dapat dibatalkan.');

        if ($leaveRequest->attachment) {
            Storage::disk('public')->delete($leaveRequest->attachment);
        }

        $leaveRequest->delete();

        return back()->with('status', 'Pengajuan berhasil dibatalkan.');
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorizeReview($request->user(), $leaveRequest);
        abort_unless($leaveRequest->status === 'pending', 403);

        $validated = $request->validate([
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $leaveRequest->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'] ?? null,
        ]);

        $count = $this->attendanceService->applyLeave($leaveRequest);
        $this->notifier->notifyLeaveDecision($leaveRequest);

        return back()->with('status', "Pengajuan disetujui. {$count} hari absensi diperbarui.");
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorizeReview($request->user(), $leaveRequest);
        abort_unless($leaveRequest->status === 'pending', 403);

        $validated = $request->validate([
            'review_note' => ['required', 'string', 'max:1000'],
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'],
        ]);

        $this->notifier->notifyLeaveDecision($leaveRequest);

        return back()->with('status', 'Pengajuan ditolak.');
    }

    private function authorizeReview(User $user, LeaveRequest $leaveRequest): void
    {
        if (in_array($user->role, ['admin', 'dev'], true)) {
            return;
        }

        abort_unless(
            $user->role === 'guru'
            && in_array($leaveRequest->student_id, $this->studentIdsForTeacher($user), true),
            403
        );
    }

    /**
     * @return array<int, int>
     */
    private function studentIdsForTeacher(User $user): array
    {
        $classIds = SchoolClass::query()
            ->where('homeroom_teacher_id', $user->id)
            ->orWhereHas('schedules', fn ($query) => $query->where('teacher_id', $user->id))
            ->pluck('id');

        return User::query()
            ->whereIn('class_id', $classIds)
            ->pluck('id')
            ->all();
    }
}
