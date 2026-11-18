<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Izin / Sakit</h2>
            @if ($canCreate)
                <a href="{{ route('leave-requests.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Ajukan Izin/Sakit</a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <form method="GET" class="grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-3">
                <select name="status" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua status</option>
                    @foreach (['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="type" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua jenis</option>
                    <option value="izin" @selected(request('type') === 'izin')>Izin</option>
                    <option value="sakit" @selected(request('type') === 'sakit')>Sakit</option>
                </select>
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
            </form>

            <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Siswa</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Jenis</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Tanggal</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Alasan</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($leaveRequests as $leaveRequest)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $leaveRequest->student?->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $leaveRequest->student?->schoolClass?->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">{{ $leaveRequest->typeLabel() }}</td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $leaveRequest->start_date->format('d M Y') }}
                                    @if (! $leaveRequest->start_date->equalTo($leaveRequest->end_date))
                                        - {{ $leaveRequest->end_date->format('d M Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <div class="max-w-xs">{{ $leaveRequest->reason }}</div>
                                    @if ($leaveRequest->attachment)
                                        <a href="{{ asset('storage/'.$leaveRequest->attachment) }}" target="_blank" class="text-xs font-medium text-indigo-600">Lihat lampiran</a>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusClass = match ($leaveRequest->status) {
                                            'approved' => 'bg-emerald-100 text-emerald-700',
                                            'rejected' => 'bg-rose-100 text-rose-700',
                                            default => 'bg-amber-100 text-amber-700',
                                        };
                                    @endphp
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">{{ $leaveRequest->statusLabel() }}</span>
                                    @if ($leaveRequest->review_note)
                                        <div class="mt-1 text-xs text-gray-500">{{ $leaveRequest->review_note }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($canReview && $leaveRequest->status === 'pending')
                                        <form method="POST" action="{{ route('leave-requests.approve', $leaveRequest) }}" class="flex flex-col items-end gap-2">
                                            @csrf
                                            <input name="review_note" placeholder="Catatan (opsional)" class="w-48 rounded-md border-gray-300 text-xs">
                                            <div class="flex gap-2">
                                                <button class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">Setujui</button>
                                                <button type="submit" formaction="{{ route('leave-requests.reject', $leaveRequest) }}" class="rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500">Tolak</button>
                                            </div>
                                        </form>
                                    @endif

                                    @if ($leaveRequest->status === 'pending' && (auth()->id() === $leaveRequest->requested_by || in_array(auth()->user()->role, ['admin', 'dev'], true)))
                                        <form method="POST" action="{{ route('leave-requests.destroy', $leaveRequest) }}" class="mt-2" onsubmit="return confirm('Batalkan pengajuan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-xs font-medium text-rose-600 hover:text-rose-500">Batalkan</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada pengajuan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $leaveRequests->links() }}
        </div>
    </div>
</x-app-layout>
