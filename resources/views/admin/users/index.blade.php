<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Pengguna</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.users.import') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Import CSV</a>
                <a href="{{ route('admin.users.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Tambah Pengguna</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @include('partials.flash')

            <form method="GET" class="grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-4">
                <input name="search" value="{{ request('search') }}" placeholder="Cari nama, email, NIS, NIP" class="rounded-md border-gray-300 text-sm">
                <select name="role" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-md border-gray-300 text-sm">
                    <option value="">Semua status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
                <button class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
            </form>

            <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Nama</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Role</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">NIS/NIP</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Kelas</th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500">Status</th>
                            <th class="px-6 py-3 text-right font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">{{ $user->roleLabel() }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $user->nis ?: $user->nip ?: '-' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $user->schoolClass?->name ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if ($user->is_active)
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Aktif</span>
                                    @else
                                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-700">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        @if (auth()->user()->role === 'dev' || ! in_array($user->role, ['dev', 'admin'], true))
                                            <a href="{{ route('admin.users.edit', $user) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Edit</a>
                                        @endif
                                        @if (auth()->user()->role === 'dev' && $user->id !== auth()->id() && $user->role !== 'dev')
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini? Data absensi ikut terhapus.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-sm font-medium text-rose-600 hover:text-rose-500">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada pengguna.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
