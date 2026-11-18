<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Admin</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.daily') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Laporan Harian</a>
                <a href="{{ route('admin.users.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Kelola Pengguna</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-gray-500">Ringkasan absensi siswa untuk {{ $today->format('d M Y') }}.</p>

            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach (['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alfa' => 'Alfa', 'belum' => 'Belum Absen'] as $key => $label)
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ $key === 'belum' ? $notYet : $counts[$key] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Ringkasan Kelas</h3>
                        <a href="{{ route('admin.classes.index') }}" class="text-sm font-medium text-indigo-600">Kelola kelas</a>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-500">Kelas</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500">Siswa</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500">Hadir</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500">Izin</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500">Sakit</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500">Alfa</th>
                                    <th class="px-3 py-2 text-center font-medium text-gray-500">Belum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($classes as $row)
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-gray-900">{{ $row['class']->name }}</td>
                                        <td class="px-3 py-2 text-center text-gray-600">{{ $row['students'] }}</td>
                                        <td class="px-3 py-2 text-center text-emerald-700">{{ $row['hadir'] }}</td>
                                        <td class="px-3 py-2 text-center text-gray-600">{{ $row['izin'] }}</td>
                                        <td class="px-3 py-2 text-center text-gray-600">{{ $row['sakit'] }}</td>
                                        <td class="px-3 py-2 text-center text-rose-700">{{ $row['alfa'] }}</td>
                                        <td class="px-3 py-2 text-center text-amber-700">{{ $row['belum'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500">Belum ada kelas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Izin Menunggu</h3>
                        <a href="{{ route('leave-requests.index', ['status' => 'pending']) }}" class="text-sm font-medium text-indigo-600">Lihat</a>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($pendingLeaves as $leave)
                            <div class="rounded-lg border border-gray-100 p-3">
                                <div class="text-sm font-medium text-gray-900">{{ $leave->student?->name }}</div>
                                <div class="text-xs text-gray-500">{{ $leave->typeLabel() }} &middot; {{ $leave->start_date->format('d M Y') }}</div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Tidak ada pengajuan menunggu.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Absensi Terbaru</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-500">Nama</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500">Kelas</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500">Masuk</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($recentAttendances as $attendance)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $attendance->user?->name }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $attendance->user?->schoolClass?->name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $attendance->time }}</td>
                                    <td class="px-3 py-2 text-gray-600">{{ $attendance->displayStatus() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">Belum ada absensi hari ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
