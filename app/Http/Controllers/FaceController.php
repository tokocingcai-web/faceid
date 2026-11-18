<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FaceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function scan(Request $request): View
    {
        $mode = match (true) {
            $request->routeIs('face.login') => 'login',
            $request->routeIs('attendance.checkout') => 'checkout',
            default => 'attendance',
        };

        return view('attendance.scan', [
            'mode' => $mode,
            'modelUrl' => config('face.model_url'),
        ]);
    }

    public function recognize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'descriptors' => ['required', 'array', 'min:1', 'max:5'],
            'descriptors.*' => ['required', 'array', 'size:128'],
            'descriptors.*.*' => ['required', 'numeric'],
            'mode' => ['nullable', 'in:attendance,login,checkout'],
        ]);

        $mode = $validated['mode'] ?? 'attendance';
        $match = $this->findBestMatch($validated['descriptors']);

        if (! $match) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wajah tidak dikenali. Pastikan wajah sudah didaftarkan terlebih dahulu.',
            ]);
        }

        $user = $match['user'];
        $result = $mode === 'checkout'
            ? $this->attendanceService->checkOut($user)
            : $this->attendanceService->checkIn($user);

        $attendance = $result['attendance'];
        $message = $result['message'];
        $redirect = null;

        if ($mode === 'login') {
            Auth::login($user);
            $request->session()->regenerate();

            $redirect = route($this->dashboardRoute($user->role));
            $message = 'Login berhasil. '.$message;
        }

        return response()->json([
            'status' => $result['status'],
            'message' => $message,
            'already_absent' => $result['status'] !== 'success' && $mode !== 'login',
            'user' => [
                'name' => $user->name,
                'role' => $user->role,
                'role_label' => $user->roleLabel(),
            ],
            'attendance' => $attendance ? [
                'date' => $attendance->date->format('Y-m-d'),
                'check_in' => $attendance->time,
                'check_out' => $attendance->check_out_time,
                'status' => $attendance->displayStatus(),
                'late_minutes' => $attendance->late_minutes,
                'early_leave_minutes' => $attendance->early_leave_minutes,
            ] : null,
            'redirect' => $redirect,
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.face', [
            'user' => $request->user(),
            'modelUrl' => config('face.model_url'),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'descriptors' => ['required', 'array', 'min:1', 'max:5'],
            'descriptors.*' => ['required', 'array', 'size:128'],
            'descriptors.*.*' => ['required', 'numeric'],
        ]);

        $request->user()->forceFill([
            'face_descriptor' => array_values($validated['descriptors']),
            'face_registered_at' => now(),
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Wajah berhasil didaftarkan. Anda sekarang dapat absen dan login menggunakan wajah.',
            ]);
        }

        return redirect()
            ->route('profile.face.edit')
            ->with('status', 'face-registered');
    }

    /**
     * @param  array<int, array<int, float|int>>  $samples
     * @return array{user: User, distance: float}|null
     */
    private function findBestMatch(array $samples): ?array
    {
        $threshold = (float) config('face.match_threshold');
        $bestUser = null;
        $bestDistance = INF;

        $users = User::query()
            ->active()
            ->whereIn('role', config('face.roles'))
            ->whereNotNull('face_descriptor')
            ->get();

        foreach ($users as $user) {
            foreach ($this->storedDescriptors($user->face_descriptor) as $stored) {
                if (count($stored) !== 128) {
                    continue;
                }

                foreach ($samples as $sample) {
                    if (count($sample) !== 128) {
                        continue;
                    }

                    $distance = $this->euclideanDistance($sample, $stored);

                    if ($distance < $bestDistance) {
                        $bestDistance = $distance;
                        $bestUser = $user;
                    }
                }
            }
        }

        if (! $bestUser || $bestDistance > $threshold) {
            return null;
        }

        return [
            'user' => $bestUser,
            'distance' => $bestDistance,
        ];
    }

    /**
     * @return array<int, array<int, float|int>>
     */
    private function storedDescriptors(mixed $stored): array
    {
        if (! is_array($stored) || $stored === []) {
            return [];
        }

        if (isset($stored[0]) && is_array($stored[0])) {
            return $stored;
        }

        return [$stored];
    }

    /**
     * @param  array<int, float|int>  $a
     * @param  array<int, float|int>  $b
     */
    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0;

        for ($i = 0; $i < 128; $i++) {
            $difference = (float) $a[$i] - (float) $b[$i];
            $sum += $difference * $difference;
        }

        return sqrt($sum);
    }

    private function dashboardRoute(string $role): string
    {
        return match ($role) {
            'guru' => 'guru.dashboard',
            'student' => 'student.dashboard',
            default => 'dashboard',
        };
    }
}
