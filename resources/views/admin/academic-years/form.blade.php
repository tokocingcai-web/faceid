<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $academicYear->exists ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <form method="POST" action="{{ $academicYear->exists ? route('admin.academic-years.update', $academicYear) : route('admin.academic-years.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($academicYear->exists)
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Tahun Ajaran</label>
                    <input name="name" value="{{ old('name', $academicYear->name) }}" placeholder="2026/2027 Ganjil" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date', $academicYear->start_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $academicYear->end_date?->format('Y-m-d')) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $academicYear->is_active)) class="rounded border-gray-300 text-indigo-600">
                    Jadikan tahun ajaran aktif
                </label>

                <div class="flex items-center gap-3">
                    <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    <a href="{{ route('admin.academic-years.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
