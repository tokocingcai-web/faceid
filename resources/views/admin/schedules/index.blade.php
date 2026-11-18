<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Jadwal Pelajaran</h2>
            <a href="{{ route('admin.schedules.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Tambah Jadwal</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <form method="GET" class="grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-5">
                <select name="academic_year_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua tahun ajaran</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
                <select name="class_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
                <select name="teacher_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua guru</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
                <select name="day_of_week" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua hari</option>
                    @foreach ($days as $number => $day)
                        <option value="{{ $number }}" @selected(request('day_of_week') == $number)>{{ $day }}</option>
                    @endforeach
                </select>
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
            </form>

            <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Hari</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Jam</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Kelas</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Mapel</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Guru</th>
                            <th class="px-6 py-3 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($schedules as $schedule)
                            <tr>
                                <td class="px-6 py-4 text-gray-700">{{ $schedule->dayName() }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $schedule->timeRange() }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $schedule->schoolClass?->name }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $schedule->subject?->name }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $schedule->teacher?->name }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.schedules.edit', $schedule) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                                        <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-medium text-rose-600 hover:text-rose-500">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada jadwal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $schedules->links() }}
        </div>
    </div>
</x-app-layout>
