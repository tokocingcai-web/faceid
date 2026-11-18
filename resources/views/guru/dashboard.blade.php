<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Guru</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.daily') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Laporan</a>
                <a href="{{ route('leave-requests.index', ['status' => 'pending']) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Izin Menunggu</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-gray-500">Ringkasan kelas Anda untuk {{ $today->format('d M Y') }}.</p>

            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach (['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alfa' => 'Alfa', 'belum' => 'Belum Absen'] as $key => $label)
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ $key === 'belum' ? $notYet : $counts[$key] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Jadwal Hari Ini</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($schedules as $schedule)
                            <div class="rounded-lg border border-gray-100 p-3">
                                <div class="font-medium text-gray-900">{{ $schedule->subject?->name }}</div>
                                <div class="text-sm text-gray-500">{{ $schedule->schoolClass?->name }} &middot; {{ $schedule->timeRange() }}</div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Tidak ada jadwal mengajar hari ini.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Izin Menunggu</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($pendingLeaves as $leave)
                            <div class="rounded-lg border border-gray-100 p-3">
                                <div class="font-medium text-gray-900">{{ $leave->student?->name }}</div>
                                <div class="text-sm text-gray-500">{{ $leave->typeLabel() }} &middot; {{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}</div>
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

            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ($classes as $class)
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="font-medium text-gray-900">{{ $class->name }}</div>
                        <div class="text-xs text-gray-500">{{ $class->academicYear?->name }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
