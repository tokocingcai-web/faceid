<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tahun Ajaran</h2>
            <a href="{{ route('admin.academic-years.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                Tambah Tahun Ajaran
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Nama</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Periode</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Kelas</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($academicYears as $year)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $year->name }}</td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $year->start_date->format('d M Y') }} - {{ $year->end_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $year->classes_count }}</td>
                                <td class="px-6 py-4">
                                    @if ($year->is_active)
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Aktif</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        @unless ($year->is_active)
                                            <form method="POST" action="{{ route('admin.academic-years.activate', $year) }}">
                                                @csrf
                                                <button class="text-sm font-medium text-emerald-600 hover:text-emerald-500">Aktifkan</button>
                                            </form>
                                        @endunless
                                        <a href="{{ route('admin.academic-years.edit', $year) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                                        <form method="POST" action="{{ route('admin.academic-years.destroy', $year) }}" onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-medium text-rose-600 hover:text-rose-500">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada tahun ajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $academicYears->links() }}
        </div>
    </div>
</x-app-layout>
