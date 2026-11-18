<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $class->exists ? 'Edit Kelas' : 'Tambah Kelas' }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <form method="POST" action="{{ $class->exists ? route('admin.classes.update', $class) : route('admin.classes.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($class->exists)
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Kelas</label>
                    <input name="name" value="{{ old('name', $class->name) }}" placeholder="X IPA 1" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tingkat</label>
                        <input name="level" value="{{ old('level', $class->level) }}" placeholder="X / XI / XII" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jurusan</label>
                        <input name="major" value="{{ old('major', $class->major) }}" placeholder="IPA / IPS" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                    <select name="academic_year_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="">Pilih tahun ajaran</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" @selected(old('academic_year_id', $class->academic_year_id) == $year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Wali Kelas</label>
                    <select name="homeroom_teacher_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Belum ditentukan</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('homeroom_teacher_id', $class->homeroom_teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    <a href="{{ route('admin.classes.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
