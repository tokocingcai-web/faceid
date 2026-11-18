<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Koreksi Absensi</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">{{ $student->name }}</h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $student->nis ?? '-' }} &middot; {{ $student->schoolClass?->name ?? 'Tanpa kelas' }}
                </p>
                <p class="mt-1 text-sm text-gray-500">Tanggal: {{ $date->format('d M Y') }}</p>
            </div>

            <form method="POST" action="{{ route('attendance-manage.update', $student) }}" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @method('PUT')
                <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alfa' => 'Alfa'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $attendance?->status ?? 'hadir') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jam Masuk</label>
                        <input type="time" name="time" value="{{ old('time', $attendance && $attendance->time !== '00:00:00' ? substr($attendance->time, 0, 5) : '') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" @required(old('status', $attendance?->status ?? 'hadir') === 'hadir')>
                        <p class="mt-1 text-xs text-gray-500">Wajib diisi jika status Hadir.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jam Pulang</label>
                        <input type="time" name="check_out_time" value="{{ old('check_out_time', $attendance?->check_out_time ? substr($attendance->check_out_time, 0, 5) : '') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Catatan</label>
                        <input name="notes" value="{{ old('notes', $attendance?->notes) }}" placeholder="Contoh: izin kegiatan sekolah" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                @if ($attendance)
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 text-sm text-gray-600">
                        Tercatat: {{ $attendance->displayStatus() }}
                        @if ($attendance->late_minutes)
                            &middot; terlambat {{ $attendance->late_minutes }} menit
                        @endif
                        @if ($attendance->early_leave_minutes)
                            &middot; pulang cepat {{ $attendance->early_leave_minutes }} menit
                        @endif
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-3">
                    <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    <a href="{{ route('attendance-manage.index', ['date' => $date->format('Y-m-d'), 'class_id' => $student->class_id]) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                </div>
            </form>

            @if ($attendance)
                <form method="POST" action="{{ route('attendance-manage.destroy', ['student' => $student, 'date' => $date->format('Y-m-d')]) }}" class="rounded-lg border border-rose-100 bg-rose-50 p-6" onsubmit="return confirm('Reset catatan absensi siswa ini?')">
                    @csrf
                    @method('DELETE')
                    <h3 class="text-sm font-semibold text-rose-800">Reset Absensi</h3>
                    <p class="mt-1 text-sm text-rose-700">Hapus catatan absensi siswa pada tanggal ini.</p>
                    <button class="mt-3 rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Reset Absensi</button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
