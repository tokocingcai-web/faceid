<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Relasi Wali Murid</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Relasi</h3>
                <form method="POST" action="{{ route('admin.parent-links.store') }}" class="mt-4 grid gap-3 md:grid-cols-5">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Wali Murid</label>
                        <select name="parent_id" class="mt-1 w-full rounded-md border-gray-300 text-sm" required>
                            <option value="">Pilih wali</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Siswa</label>
                        <select name="student_id" class="mt-1 w-full rounded-md border-gray-300 text-sm" required>
                            <option value="">Pilih siswa</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->name }} - {{ $student->schoolClass?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hubungan</label>
                        <input name="relationship" value="{{ old('relationship') }}" placeholder="Ayah/Ibu" class="mt-1 w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div class="flex items-end md:col-span-5">
                        <label class="mr-4 inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="is_primary" value="0">
                            <input type="checkbox" name="is_primary" value="1" class="rounded border-gray-300 text-indigo-600">
                            Wali utama
                        </label>
                        <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan Relasi</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Wali Murid</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Siswa</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Kelas</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Hubungan</th>
                            <th class="px-6 py-3 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($links as $parent)
                            @forelse ($parent->children as $child)
                                <tr>
                                    <td class="px-6 py-4 text-gray-900">{{ $parent->name }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $child->name }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $child->schoolClass?->name }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $child->pivot->relationship ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <form method="POST" action="{{ route('admin.parent-links.destroy', [$parent, $child]) }}" onsubmit="return confirm('Hapus relasi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-medium text-rose-600 hover:text-rose-500">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 text-gray-900">{{ $parent->name }}</td>
                                    <td colspan="4" class="px-6 py-4 text-gray-500">Belum memiliki siswa terhubung.</td>
                                </tr>
                            @endforelse
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada relasi wali murid.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $links->links() }}
        </div>
    </div>
</x-app-layout>
