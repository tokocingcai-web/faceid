<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Pengguna</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('admin.users.form', ['user' => $user])

            <form id="reset-password" method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
                @csrf
                <h3 class="text-lg font-semibold text-gray-900">Reset Password</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                        <input type="password" name="password" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                </div>
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Reset Password</button>
            </form>
        </div>
    </div>
</x-app-layout>
