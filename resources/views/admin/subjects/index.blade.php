<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mata Pelajaran</h2>
            <a href="{{ route('admin.subjects.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Tambah Mapel</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Kode</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Nama</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Jadwal</th>
                            <th class="px-6 py-3 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($subjects as $subject)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $subject->code }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $subject->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $subject->schedules_count }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                                        <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-medium text-rose-600 hover:text-rose-500">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada mata pelajaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $subjects->links() }}
        </div>
    </div>
</x-app-layout>
