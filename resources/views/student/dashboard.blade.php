<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Halo, {{ auth()->user()->name }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Kelas: {{ auth()->user()->schoolClass?->name ?? 'Belum memiliki kelas' }}
                </p>
            </div>

            @include('partials.attendance-summary')

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Pengajuan Izin/Sakit</h3>
                    <a href="{{ route('leave-requests.create') }}" class="text-sm font-medium text-indigo-600">Ajukan baru</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($leaveRequests as $leave)
                        <div class="rounded-lg border border-gray-100 p-3">
                            <div class="text-sm font-medium text-gray-900">{{ $leave->typeLabel() }} &middot; {{ $leave->statusLabel() }}</div>
                            <div class="text-xs text-gray-500">{{ $leave->start_date->format('d M Y') }} - {{ $leave->end_date->format('d M Y') }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada pengajuan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
