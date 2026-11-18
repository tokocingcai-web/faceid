<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelas</h2>
            <a href="{{ route('admin.classes.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Tambah Kelas</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Kelas</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Tahun Ajaran</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Wali Kelas</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Siswa</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Jadwal</th>
                            <th class="px-6 py-3 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($classes as $class)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $class->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $class->level }} {{ $class->major }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $class->academicYear?->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $class->homeroomTeacher?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $class->students_count }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $class->schedules_count }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.classes.edit', $class) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                                        <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" onsubmit="return confirm('Hapus kelas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-medium text-rose-600 hover:text-rose-500">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada kelas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $classes->links() }}
        </div>
    </div>
</x-app-layout>
