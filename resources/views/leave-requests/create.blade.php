<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ajukan Izin/Sakit</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <form method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf

                @if (auth()->user()->role === 'wali')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Siswa</label>
                        <select name="student_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Pilih siswa</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis</label>
                        <select name="type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="izin" @selected(old('type') === 'izin')>Izin</option>
                            <option value="sakit" @selected(old('type') === 'sakit')>Sakit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Alasan</label>
                    <textarea name="reason" rows="4" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>{{ old('reason') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Lampiran (opsional)</label>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 w-full text-sm text-gray-600">
                    <p class="mt-1 text-xs text-gray-500">PDF atau gambar, maksimal 2 MB.</p>
                </div>

                <div class="flex items-center gap-3">
                    <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Kirim Pengajuan</button>
                    <a href="{{ route('leave-requests.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
