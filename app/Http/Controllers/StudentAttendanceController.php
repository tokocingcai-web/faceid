<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'status' => ['nullable', 'in:hadir,terlambat,izin,sakit,alfa'],
        ]);

        $monthStr = $request->input('month', now()->timezone(config('face.timezone'))->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $monthStr);

        $allMonthly = Attendance::query()
            ->where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->get();

        $hadirTepat = $allMonthly->where('status', 'hadir')->where('late_minutes', 0)->count();
        $terlambat = $allMonthly->where('late_minutes', '>', 0)->count();
        $izin = $allMonthly->where('status', 'izin')->count();
        $sakit = $allMonthly->where('status', 'sakit')->count();
        $alfa = $allMonthly->where('status', 'alfa')->count();
        $totalRecords = $allMonthly->count();
        $percentage = $totalRecords > 0
            ? round((($hadirTepat + $terlambat) / $totalRecords) * 100, 1)
            : 0;

        $query = Attendance::query()
            ->where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month);

        $statusFilter = $request->input('status');
        if ($statusFilter === 'terlambat') {
            $query->where('late_minutes', '>', 0);
        } elseif ($statusFilter === 'hadir') {
            $query->where('status', 'hadir')->where('late_minutes', 0);
        } elseif ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $records = $query->orderByDesc('date')
            ->orderByDesc('time')
            ->paginate(15)
            ->withQueryString();

        return view('student.attendance.history', [
            'records' => $records,
            'month' => $monthStr,
            'date' => $date,
            'statusFilter' => $statusFilter,
            'stats' => [
                'hadir' => $hadirTepat,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'alfa' => $alfa,
                'total' => $totalRecords,
                'percentage' => $percentage,
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $monthStr = $request->input('month', now()->timezone(config('face.timezone'))->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $monthStr);

        $records = Attendance::query()
            ->where('user_id', $user->id)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->orderBy('date')
            ->get();

        $filename = 'riwayat-absen-'.($user->nis ?: $user->id).'-'.$monthStr.'.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status', 'Terlambat (menit)', 'Pulang Cepat (menit)', 'Keterangan']);

            foreach ($records as $row) {
                fputcsv($handle, [
                    $row->date->format('Y-m-d'),
                    $row->time,
                    $row->check_out_time ?? '-',
                    $row->displayStatus(),
                    $row->late_minutes ?? 0,
                    $row->early_leave_minutes ?? 0,
                    $row->notes ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
