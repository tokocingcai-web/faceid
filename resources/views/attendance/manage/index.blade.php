<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Absensi</h2>
            <a href="{{ route('reports.daily', ['date' => $date->format('Y-m-d'), 'class_id' => $classId]) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Laporan Harian</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <form method="GET" class="grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-5">
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="rounded-md border-gray-300 text-sm">
                <select name="class_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected($classId == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua status</option>
                    @foreach (['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alfa' => 'Alfa', 'belum' => 'Belum Absen'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS" class="rounded-md border-gray-300 text-sm">
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Tampilkan</button>
            </form>

            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach (['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alfa' => 'Alfa', 'belum' => 'Belum Absen'] as $key => $label)
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ $summary[$key] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">NIS</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Nama</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Kelas</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Masuk</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Pulang</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Keterangan</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($students as $student)
                            @php($attendance = $attendances->get($student->id))
                            <tr>
                                <td class="px-4 py-3 text-gray-600">{{ $student->nis ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $student->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $student->schoolClass?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                        {{ $attendance?->displayStatus() ?? 'Belum Absen' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $attendance?->time !== null && $attendance->time !== '00:00:00' ? substr($attendance->time, 0, 5) : '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $attendance?->check_out_time ? substr($attendance->check_out_time, 0, 5) : '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">
                                    @if ($attendance?->late_minutes)
                                        Terlambat {{ $attendance->late_minutes }} menit
                                    @elseif ($attendance?->early_leave_minutes)
                                        Pulang cepat {{ $attendance->early_leave_minutes }} menit
                                    @elseif ($attendance?->notes)
                                        {{ $attendance->notes }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('attendance-manage.edit', ['student' => $student, 'date' => $date->format('Y-m-d')]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                                        @if ($attendance)
                                            <form method="POST" action="{{ route('attendance-manage.destroy', ['student' => $student, 'date' => $date->format('Y-m-d')]) }}" onsubmit="return confirm('Reset catatan absensi siswa ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-sm font-medium text-rose-600 hover:text-rose-500">Reset</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">Tidak ada siswa pada filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $students->links() }}
        </div>
    </div>
</x-app-layout>
