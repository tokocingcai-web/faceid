@php
    $editing = $user->exists;
    $action = $editing ? route('admin.users.update', $user) : route('admin.users.store');
@endphp

<div class="space-y-6">
    @include('partials.flash')

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">NIS</label>
                <input name="nis" value="{{ old('nis', $user->nis) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">NIP</label>
                <input name="nip" value="{{ old('nip', $user->nip) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                <select name="gender" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Belum diisi</option>
                    <option value="male" @selected(old('gender', $user->gender) === 'male')>Laki-laki</option>
                    <option value="female" @selected(old('gender', $user->gender) === 'female')>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Telepon</label>
                <input name="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Kelas</label>
                <select name="class_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Tidak ada kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(old('class_id', $user->class_id) == $class->id)>{{ $class->name }} - {{ $class->academicYear?->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Alamat</label>
            <textarea name="address" rows="2" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('address', $user->address) }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Foto</label>
                <input type="file" name="photo" accept="image/*" class="mt-1 w-full text-sm text-gray-600">
                @if ($user->photo)
                    <p class="mt-2 text-xs text-gray-500">Foto saat ini: {{ $user->photo }}</p>
                @endif
            </div>
            <div class="space-y-4">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing ? $user->is_active : true)) class="rounded border-gray-300 text-indigo-600">
                    Akun aktif
                </label>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password {{ $editing ? '(kosongkan jika tidak diubah)' : '' }}</label>
                    <input type="password" name="password" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" @required(! $editing)>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" @required(! $editing)>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</a>
        </div>
    </form>
</div>
