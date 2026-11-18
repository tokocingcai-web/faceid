<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Riwayat Absensi') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('student.attendance.export', ['month' => $month]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export CSV
                </a>
                <a href="{{ route('dashboard') }}" class="inline-flex rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <form method="GET" action="{{ route('student.attendance.index') }}" class="flex flex-wrap items-end gap-4">
                    <div class="w-full sm:w-auto">
                        <label for="month" class="block text-xs font-medium text-gray-600 mb-1">Pilih Bulan</label>
                        <input type="month" id="month" name="month" value="{{ $month }}" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="w-full sm:w-auto">
                        <label for="status" class="block text-xs font-medium text-gray-600 mb-1">Status Absensi</label>
                        <select id="status" name="status" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Status</option>
                            <option value="hadir" @selected($statusFilter === 'hadir')>Hadir (Tepat Waktu)</option>
                            <option value="terlambat" @selected($statusFilter === 'terlambat')>Terlambat</option>
                            <option value="izin" @selected($statusFilter === 'izin')>Izin</option>
                            <option value="sakit" @selected($statusFilter === 'sakit')>Sakit</option>
                            <option value="alfa" @selected($statusFilter === 'alfa')>Alfa</option>
                        </select>
                    </div>

                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">
                        Terapkan
                    </button>

                    @if (request()->hasAny(['month', 'status']))
                        <a href="{{ route('student.attendance.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 self-center">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Monthly Summary Cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Tepat Waktu</div>
                    <div class="mt-2 text-2xl font-bold text-emerald-600">{{ $stats['hadir'] }}</div>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Terlambat</div>
                    <div class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['terlambat'] }}</div>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Izin</div>
                    <div class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['izin'] }}</div>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Sakit</div>
                    <div class="mt-2 text-2xl font-bold text-purple-600">{{ $stats['sakit'] }}</div>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Alfa</div>
                    <div class="mt-2 text-2xl font-bold text-rose-600">{{ $stats['alfa'] }}</div>
                </div>

                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Kehadiran</div>
                    <div class="mt-2 text-2xl font-bold text-indigo-600">{{ $stats['percentage'] }}%</div>
                </div>
            </div>

            <!-- Attendance Records Table -->
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">
                        Daftar Catatan Absensi &mdash; {{ $date->translatedFormat('F Y') }}
                    </h3>
                    <span class="text-xs text-gray-500">Total: {{ $records->total() }} catatan</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">Hari / Tanggal</th>
                                <th class="px-6 py-3 text-left font-medium">Jam Masuk</th>
                                <th class="px-6 py-3 text-left font-medium">Jam Pulang</th>
                                <th class="px-6 py-3 text-left font-medium">Status</th>
                                <th class="px-6 py-3 text-left font-medium">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($records as $attendance)
                                <tr class="hover:bg-gray-50/75 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $attendance->date->translatedFormat('l') }}</div>
                                        <div class="text-xs text-gray-500">{{ $attendance->date->translatedFormat('d F Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-mono text-xs">
                                        {{ $attendance->time ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-mono text-xs">
                                        {{ $attendance->check_out_time ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $badgeClass = match ($attendance->status) {
                                                'hadir' => $attendance->late_minutes > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700',
                                                'izin' => 'bg-blue-100 text-blue-700',
                                                'sakit' => 'bg-purple-100 text-purple-700',
                                                'alfa' => 'bg-rose-100 text-rose-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClass }}">
                                            {{ $attendance->displayStatus() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">
                                        @if ($attendance->late_minutes > 0)
                                            <span class="text-amber-600 font-medium">Terlambat {{ $attendance->late_minutes }} menit</span>
                                        @endif
                                        @if ($attendance->early_leave_minutes > 0)
                                            <span class="text-amber-600 font-medium block">Pulang cepat {{ $attendance->early_leave_minutes }} menit</span>
                                        @endif
                                        @if ($attendance->notes)
                                            <span class="text-gray-600 block">{{ $attendance->notes }}</span>
                                        @endif
                                        @if (! $attendance->late_minutes && ! $attendance->early_leave_minutes && ! $attendance->notes)
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="mt-2 text-sm text-gray-500">Tidak ada catatan absensi untuk periode ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($records->hasPages())
                    <div class="border-t border-gray-100 p-4">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
