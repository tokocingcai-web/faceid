@php
    $user = auth()->user();
    $today = now()->timezone(config('face.timezone'))->toDateString();
    $todayAttendance = $user->attendances()->whereDate('date', $today)->first();
    $recentAttendances = $user->attendances()->orderByDesc('date')->orderByDesc('time')->limit(10)->get();
@endphp

<div class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Absensi Hari Ini</h3>

            @if ($todayAttendance)
                <p class="mt-3 text-2xl font-bold {{ $todayAttendance->status === 'hadir' ? ($todayAttendance->late_minutes > 0 ? 'text-amber-600' : 'text-emerald-600') : 'text-gray-700' }}">
                    {{ $todayAttendance->displayStatus() }}
                </p>
                <p class="mt-1 text-sm text-gray-600">
                    Masuk {{ $todayAttendance->time }}
                    @if ($todayAttendance->check_out_time)
                        &middot; Pulang {{ $todayAttendance->check_out_time }}
                    @endif
                </p>

                @if ($todayAttendance->status === 'hadir' && ! $todayAttendance->check_out_time)
                    <a href="{{ route('attendance.checkout') }}" class="mt-4 inline-flex rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                        Absen Pulang
                    </a>
                @endif
            @else
                <p class="mt-3 text-2xl font-bold text-amber-600">Belum Absen</p>
                <p class="mt-1 text-sm text-gray-600">Lakukan absensi menggunakan kamera.</p>
                <a href="{{ route('attendance.scan') }}" class="mt-4 inline-flex rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Absensi Sekarang
                </a>
            @endif
        </div>

        <div class="rounded-lg bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Face ID & Izin</h3>

            @if (in_array($user->role, config('face.roles'), true))
                @if ($user->hasRegisteredFace())
                    <p class="mt-3 text-sm text-emerald-700">Wajah sudah terdaftar dan siap digunakan.</p>
                @else
                    <p class="mt-3 text-sm text-amber-700">Wajah belum terdaftar.</p>
                @endif
            @endif

            <div class="mt-4 flex flex-wrap gap-2">
                @if (in_array($user->role, config('face.roles'), true))
                    <a href="{{ route('profile.face.edit') }}" class="inline-flex rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        {{ $user->hasRegisteredFace() ? 'Kelola Wajah' : 'Daftarkan Wajah' }}
                    </a>
                @endif

                <a href="{{ route('leave-requests.index') }}" class="inline-flex rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Izin/Sakit
                </a>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <div class="border-b border-gray-100 p-6 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Riwayat Absensi</h3>
            @if ($user->role === 'student')
                <a href="{{ route('student.attendance.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Lihat semua</a>
            @endif
        </div>

        @if ($recentAttendances->isEmpty())
            <p class="p-6 text-sm text-gray-500">Belum ada data absensi.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Tanggal</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Masuk</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Pulang</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recentAttendances as $attendance)
                            <tr>
                                <td class="px-6 py-3 text-gray-700">{{ $attendance->date->format('d M Y') }}</td>
                                <td class="px-6 py-3 text-gray-700">{{ $attendance->time }}</td>
                                <td class="px-6 py-3 text-gray-700">{{ $attendance->check_out_time ?? '-' }}</td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                        {{ $attendance->displayStatus() }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-500">
                                    @if ($attendance->late_minutes)
                                        Terlambat {{ $attendance->late_minutes }} menit
                                    @elseif ($attendance->early_leave_minutes)
                                        Pulang cepat {{ $attendance->early_leave_minutes }} menit
                                    @elseif ($attendance->notes)
                                        {{ $attendance->notes }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
