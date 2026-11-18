<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (in_array($user->role, config('face.roles'), true))
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h2 class="text-lg font-medium text-gray-900">Face ID</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Daftarkan wajah agar dapat melakukan absensi dan login tanpa memasukkan email atau password.
                        </p>

                        <div class="mt-4 flex items-center gap-3">
                            @if ($user->hasRegisteredFace())
                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700">
                                    Wajah sudah terdaftar
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700">
                                    Wajah belum terdaftar
                                </span>
                            @endif

                            <a href="{{ route('profile.face.edit') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                {{ $user->hasRegisteredFace() ? 'Perbarui wajah' : 'Daftarkan wajah' }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
