<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur-xl">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex min-w-0 items-center gap-6">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                            <x-application-logo class="h-6 w-6" />
                        </span>
                        <span class="truncate text-sm font-semibold tracking-tight text-slate-900">{{ config('app.name', 'FaceID Absen') }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden items-center gap-1 md:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if (in_array(Auth::user()->role, config('face.roles'), true))
                        <x-nav-link :href="route('attendance.scan')" :active="request()->routeIs('attendance.scan')">
                            {{ __('Absensi Wajah') }}
                        </x-nav-link>

                        <x-nav-link :href="route('attendance.checkout')" :active="request()->routeIs('attendance.checkout')">
                            {{ __('Absen Pulang') }}
                        </x-nav-link>
                    @endif

                    <x-nav-link :href="route('leave-requests.index')" :active="request()->routeIs('leave-requests.*')">
                        {{ __('Izin/Sakit') }}
                    </x-nav-link>

                    @if (in_array(Auth::user()->role, ['admin', 'dev', 'guru'], true))
                        <x-nav-link :href="route('attendance-manage.index')" :active="request()->routeIs('attendance-manage.*')">
                            {{ __('Data Absensi') }}
                        </x-nav-link>

                        <x-nav-link :href="route('reports.daily')" :active="request()->routeIs('reports.*')">
                            {{ __('Laporan') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden items-center md:flex md:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2.5 rounded-xl px-2 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus:outline-none">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-xs font-semibold text-indigo-700">
                                {{ mb_substr(Auth::user()->name, 0, 1) }}
                            </span>
                            <span class="hidden max-w-[10rem] truncate lg:block">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if (in_array(Auth::user()->role, ['admin', 'dev'], true))
                            <div class="block px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                {{ __('Manajemen') }}
                            </div>
                            <x-dropdown-link :href="route('admin.users.index')">{{ __('Pengguna') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.academic-years.index')">{{ __('Tahun Ajaran') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.classes.index')">{{ __('Kelas') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.subjects.index')">{{ __('Mata Pelajaran') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.schedules.index')">{{ __('Jadwal') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.parent-links.index')">{{ __('Relasi Wali') }}</x-dropdown-link>
                            <div class="my-2 border-t border-gray-100"></div>
                        @endif

                        @if (in_array(Auth::user()->role, config('face.roles'), true))
                            <x-dropdown-link :href="route('profile.face.edit')">
                                {{ __('Face ID') }}
                            </x-dropdown-link>
                        @endif

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center md:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-slate-100 md:hidden">
        <div class="space-y-1 px-2 pb-3 pt-3">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if (in_array(Auth::user()->role, config('face.roles'), true))
                <x-responsive-nav-link :href="route('attendance.scan')" :active="request()->routeIs('attendance.scan')">
                    {{ __('Absensi Wajah') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('attendance.checkout')" :active="request()->routeIs('attendance.checkout')">
                    {{ __('Absen Pulang') }}
                </x-responsive-nav-link>
            @endif

            <x-responsive-nav-link :href="route('leave-requests.index')" :active="request()->routeIs('leave-requests.*')">
                {{ __('Izin/Sakit') }}
            </x-responsive-nav-link>

            @if (in_array(Auth::user()->role, ['admin', 'dev', 'guru'], true))
                <x-responsive-nav-link :href="route('attendance-manage.index')" :active="request()->routeIs('attendance-manage.*')">
                    {{ __('Data Absensi') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('reports.daily')" :active="request()->routeIs('reports.*')">
                    {{ __('Laporan') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-slate-100 pb-2 pt-4">
            <div class="flex items-center gap-3 px-4 pb-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-sm font-semibold text-indigo-700">{{ mb_substr(Auth::user()->name, 0, 1) }}</span>
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                    <div class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                @if (in_array(Auth::user()->role, ['admin', 'dev'], true))
                    <x-responsive-nav-link :href="route('admin.users.index')">{{ __('Pengguna') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.academic-years.index')">{{ __('Tahun Ajaran') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.classes.index')">{{ __('Kelas') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.subjects.index')">{{ __('Mata Pelajaran') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.schedules.index')">{{ __('Jadwal') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.parent-links.index')">{{ __('Relasi Wali') }}</x-responsive-nav-link>
                @endif

                @if (in_array(Auth::user()->role, config('face.roles'), true))
                    <x-responsive-nav-link :href="route('profile.face.edit')">
                        {{ __('Face ID') }}
                    </x-responsive-nav-link>
                @endif

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
