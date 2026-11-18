<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Harian</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.monthly') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Laporan Bulanan</a>
                <a href="{{ route('reports.daily.export', ['date' => $date->format('Y-m-d'), 'class_id' => $classId]) }}" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Export CSV</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" class="grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-4">
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="rounded-md border-gray-300 text-sm">
                <select name="class_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected($classId == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Tampilkan</button>
                <button type="button" onclick="window.print()" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cetak / PDF</button>
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
                            <th class="px-6 py-3 text-left font-medium text-gray-500">NIS</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Nama</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Kelas</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Masuk</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Pulang</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Keterlambatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-6 py-3 text-gray-600">{{ $row['student']->nis ?? '-' }}</td>
                                <td class="px-6 py-3 font-medium text-gray-900">{{ $row['student']->name }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $row['student']->schoolClass?->name ?? '-' }}</td>
                                <td class="px-6 py-3">
                                    @php($attendance = $row['attendance'])
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                        {{ $attendance?->displayStatus() ?? 'Belum Absen' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-600">{{ $attendance?->time ?? '-' }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $attendance?->check_out_time ?? '-' }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $attendance?->late_minutes ? $attendance->late_minutes.' menit' : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Tidak ada siswa pada filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
