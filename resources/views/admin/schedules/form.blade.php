<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $schedule->exists ? 'Edit Jadwal' : 'Tambah Jadwal' }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <form method="POST" action="{{ $schedule->exists ? route('admin.schedules.update', $schedule) : route('admin.schedules.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($schedule->exists)
                    @method('PUT')
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                        <select name="academic_year_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Pilih tahun ajaran</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}" @selected(old('academic_year_id', $schedule->academic_year_id) == $year->id)>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <select name="class_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Pilih kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(old('class_id', $schedule->class_id) == $class->id)>{{ $class->name }} - {{ $class->academicYear?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                        <select name="subject_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Pilih mapel</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id', $schedule->subject_id) == $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Guru</label>
                        <select name="teacher_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Pilih guru</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id', $schedule->teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hari</label>
                        <select name="day_of_week" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                            @foreach ($days as $number => $day)
                                <option value="{{ $number }}" @selected(old('day_of_week', $schedule->day_of_week) == $number)>{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mulai</label>
                        <input type="time" name="start_time" value="{{ old('start_time', $schedule->start_time ? substr($schedule->start_time, 0, 5) : '') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Selesai</label>
                        <input type="time" name="end_time" value="{{ old('end_time', $schedule->end_time ? substr($schedule->end_time, 0, 5) : '') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ruang</label>
                        <input name="room" value="{{ old('room', $schedule->room) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    <a href="{{ route('admin.schedules.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
