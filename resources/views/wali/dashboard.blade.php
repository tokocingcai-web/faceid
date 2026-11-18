<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Wali Murid</h2>
            <a href="{{ route('leave-requests.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Ajukan Izin/Sakit</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-gray-500">Pantau kehadiran anak Anda untuk {{ $today->format('d M Y') }}.</p>

            <div class="grid gap-6">
                @forelse ($summaries as $summary)
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $summary['student']->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $summary['student']->schoolClass?->name ?? 'Belum memiliki kelas' }}</p>
                            </div>
                            @if ($summary['today'])
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">
                                    Hari ini: {{ $summary['today']->displayStatus() }}
                                    ({{ $summary['today']->time }})
                                </span>
                            @else
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700">Belum absen hari ini</span>
                            @endif
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-5">
                            @foreach (['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alfa' => 'Alfa'] as $key => $label)
                                <div class="rounded-lg border border-gray-100 p-3">
                                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $label }}</div>
                                    <div class="mt-1 text-xl font-bold text-gray-900">{{ $summary[$key] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg bg-white p-6 text-sm text-gray-500 shadow-sm">
                        Belum ada siswa yang terhubung dengan akun wali ini. Hubungi admin untuk menambahkan relasi.
                    </div>
                @endforelse
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Pengajuan Terakhir</h3>
                    <a href="{{ route('leave-requests.index') }}" class="text-sm font-medium text-indigo-600">Lihat semua</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($leaveRequests as $leave)
                        <div class="rounded-lg border border-gray-100 p-3">
                            <div class="text-sm font-medium text-gray-900">{{ $leave->student?->name }} &middot; {{ $leave->typeLabel() }}</div>
                            <div class="text-xs text-gray-500">{{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }} &middot; {{ $leave->statusLabel() }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada pengajuan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
