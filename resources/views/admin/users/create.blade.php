<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pengguna</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include('admin.users.form', ['user' => $user])
        </div>
    </div>
</x-app-layout>
