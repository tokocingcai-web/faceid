<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $subject->exists ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran' }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <form method="POST" action="{{ $subject->exists ? route('admin.subjects.update', $subject) : route('admin.subjects.store') }}" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                @if ($subject->exists)
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode</label>
                    <input name="code" value="{{ old('code', $subject->code) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Mata Pelajaran</label>
                    <input name="name" value="{{ old('name', $subject->name) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                </div>

                <div class="flex items-center gap-3">
                    <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    <a href="{{ route('admin.subjects.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
