<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // Menampilkan halaman kamera scanner
    public function index()
    {
        return view('attendance.scan');
    }

    // Menyimpan data absen saat wajah terdeteksi
    public function store(Request $request)
    {
        // Validasi data user berdasarkan email atau ID yang dikenali dari hasil scan wajah
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Cek apakah user sudah melakukan absen hari ini
        $today = Carbon::now()->toDateString();
        $alreadyAbsen = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($alreadyAbsen) {
            return response()->json([
                'status' => 'warning',
                'message' => "{$user->name} sudah melakukan absensi hari ini pada pukul {$alreadyAbsen->time}.",
            ]);
        }

        // Catat absen baru
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'time' => Carbon::now()->toTimeString(),
            'status' => 'hadir',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Absensi berhasil! Selamat datang, {$user->name} ({$user->role}).",
        ]);
    }
}
