<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Import Pengguna</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Format CSV</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Kolom: <code>name, email, role, nis, nip, class, gender, phone, password</code>.
                    Role yang didukung: guru, student, wali. Nama kelas harus sama dengan data kelas.
                </p>
                <a href="{{ route('admin.users.import.template') }}" class="mt-3 inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-500">Unduh template CSV</a>
            </div>

            <form method="POST" action="{{ route('admin.users.import.store') }}" enctype="multipart/form-data" class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">File CSV</label>
                    <input type="file" name="file" accept=".csv,text/csv" class="mt-1 w-full text-sm text-gray-600" required>
                </div>
                <div class="flex items-center gap-3">
                    <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Import</button>
                    <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
