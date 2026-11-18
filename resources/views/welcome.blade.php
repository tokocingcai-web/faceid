<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Sistem absensi sekolah berbasis pengenalan wajah dengan izin online, laporan otomatis, dan notifikasi wali murid.">
        <title>{{ config('app.name', 'FaceID Absen') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif

        <style>
            @keyframes scan {
                0%, 100% { transform: translateY(-74px); opacity: 0; }
                15%, 85% { opacity: 1; }
                50% { transform: translateY(74px); }
            }
            .scan-line { animation: scan 3.2s ease-in-out infinite; }
        </style>
    </head>
    <body class="bg-slate-950 font-sans text-white antialiased">
        <header class="fixed inset-x-0 top-0 z-50 border-b border-white/10 bg-slate-950/80 backdrop-blur-xl">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-500/15 text-indigo-300 ring-1 ring-inset ring-indigo-400/30">
                        <x-application-logo class="h-7 w-7" />
                    </span>
                    <span class="text-base font-semibold tracking-tight">{{ config('app.name', 'FaceID Absen') }}</span>
                </a>

                <nav class="hidden items-center gap-8 text-sm text-slate-300 md:flex">
                    <a href="#fitur" class="transition hover:text-white">Fitur</a>
                    <a href="#cara-kerja" class="transition hover:text-white">Cara Kerja</a>
                    <a href="{{ route('attendance.scan') }}" class="transition hover:text-white">Absensi Wajah</a>
                </nav>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden rounded-xl px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-white/10 sm:inline-flex">
                            Masuk
                        </a>
                        <a href="{{ route('attendance.scan') }}" class="rounded-xl bg-indigo-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:bg-indigo-400">
                            Absen Sekarang
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main class="relative overflow-hidden pt-16">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(99,102,241,0.22),transparent_45%),radial-gradient(circle_at_left,rgba(14,165,233,0.12),transparent_35%)]"></div>
            <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.06)_1px,transparent_1px)] bg-[length:56px_56px] [mask-image:linear-gradient(to_bottom,black,transparent_85%)]"></div>

            <section class="relative mx-auto grid max-w-7xl gap-14 px-4 pb-20 pt-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:px-8 lg:pb-28 lg:pt-24">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-indigo-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Sistem Absensi Sekolah
                    </span>

                    <h1 class="mt-6 max-w-3xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">
                        Absensi wajah yang
                        <span class="bg-gradient-to-r from-indigo-300 via-sky-300 to-emerald-300 bg-clip-text text-transparent">cepat, akurat,</span>
                        dan terintegrasi.
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-8 text-slate-400 sm:text-lg">
                        Catat kehadiran siswa dan guru hanya dengan wajah. Pantau keterlambatan, kelola izin, dan kirim laporan ke wali murid dalam satu sistem.
                    </p>

                    <div class="mt-9 flex flex-wrap items-center gap-3">
                        <a href="{{ route('attendance.scan') }}" class="inline-flex items-center gap-2 rounded-2xl bg-indigo-500 px-6 py-3.5 text-sm font-semibold text-white shadow-xl shadow-indigo-500/25 transition hover:-translate-y-0.5 hover:bg-indigo-400">
                            Mulai Absensi
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0-6-6m6 6-6 6" /></svg>
                        </a>
                        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">
                            {{ auth()->check() ? 'Buka Dashboard' : 'Masuk ke Akun' }}
                        </a>
                    </div>

                    <dl class="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-white/10 pt-6">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Deteksi</dt>
                            <dd class="mt-1 text-lg font-semibold">Real-time</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Pencatatan</dt>
                            <dd class="mt-1 text-lg font-semibold">Otomatis</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-slate-500">Laporan</dt>
                            <dd class="mt-1 text-lg font-semibold">CSV & PDF</dd>
                        </div>
                    </dl>
                </div>

                <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                    <div class="absolute -inset-6 rounded-[2.5rem] bg-gradient-to-br from-indigo-500/30 via-sky-500/10 to-emerald-500/20 blur-2xl"></div>

                    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/90 p-5 shadow-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Kamera aktif
                            </div>
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] text-slate-400">Mesin pengenalan wajah</span>
                        </div>

                        <div class="relative mt-5 aspect-[4/3] overflow-hidden rounded-[1.5rem] border border-indigo-400/20 bg-gradient-to-b from-slate-800 to-slate-950">
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(99,102,241,0.18),transparent_65%)]"></div>
                            <div class="scan-line absolute inset-x-10 top-1/2 h-px bg-gradient-to-r from-transparent via-indigo-300 to-transparent"></div>

                            <div class="absolute left-6 top-6 h-10 w-10 rounded-tl-xl border-l-2 border-t-2 border-indigo-300/70"></div>
                            <div class="absolute right-6 top-6 h-10 w-10 rounded-tr-xl border-r-2 border-t-2 border-indigo-300/70"></div>
                            <div class="absolute bottom-6 left-6 h-10 w-10 rounded-bl-xl border-b-2 border-l-2 border-indigo-300/70"></div>
                            <div class="absolute bottom-6 right-6 h-10 w-10 rounded-br-xl border-b-2 border-r-2 border-indigo-300/70"></div>

                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <div class="flex h-24 w-24 items-center justify-center rounded-full border border-white/10 bg-white/5 text-indigo-200">
                                    <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0" />
                                    </svg>
                                </div>
                                <p class="mt-4 text-sm font-medium text-slate-200">Posisikan wajah di dalam bingkai</p>
                                <p class="mt-1 text-xs text-slate-500">Pastikan pencahayaan cukup</p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 p-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-300">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-100">Absensi berhasil dicatat</p>
                                    <p class="text-xs text-emerald-200/60">Masuk pukul 07:04 &middot; Tepat waktu</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3 text-center">
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                                <p class="text-xs text-slate-500">Hadir hari ini</p>
                                <p class="mt-1 text-xl font-semibold text-white">96%</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                                <p class="text-xs text-slate-500">Tercatat</p>
                                <p class="mt-1 text-xl font-semibold text-white">248</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="fitur" class="relative border-t border-white/10 bg-slate-950/60 py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-300">Fitur utama</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Semua kebutuhan absensi sekolah dalam satu tempat.</h2>
                        <p class="mt-4 text-base leading-7 text-slate-400">Dirancang untuk admin, guru, siswa, dan wali murid dengan alur kerja yang sederhana.</p>
                    </div>

                    <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @php
                            $features = [
                                ['title' => 'Absensi Wajah', 'description' => 'Check-in dan check-out tanpa kartu. Face recognition berjalan langsung dari kamera.', 'icon' => 'scan'],
                                ['title' => 'Izin & Sakit', 'description' => 'Siswa dan wali mengajukan izin online, guru menyetujui dengan catatan.', 'icon' => 'document'],
                                ['title' => 'Laporan Otomatis', 'description' => 'Rekap harian dan bulanan siap diekspor CSV atau dicetak sebagai PDF.', 'icon' => 'chart'],
                                ['title' => 'Notifikasi Wali', 'description' => 'Pemberitahuan terlambat, alfa, dan keputusan izin langsung ke wali.', 'icon' => 'bell'],
                            ];
                        @endphp

                        @foreach ($features as $feature)
                            <div class="group rounded-2xl border border-white/10 bg-white/[0.03] p-6 transition hover:-translate-y-1 hover:border-indigo-400/30 hover:bg-white/[0.06]">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500/15 text-indigo-300">
                                    @if ($feature['icon'] === 'scan')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5V5.25A2.25 2.25 0 0 1 6 3h2.25M17.25 3H19.5A2.25 2.25 0 0 1 21.75 5.25V7.5M21.75 16.5v2.25A2.25 2.25 0 0 1 19.5 21h-2.25M6.75 21H4.5A2.25 2.25 0 0 1 2.25 18.75V16.5M8.25 12h7.5" /></svg>
                                    @elseif ($feature['icon'] === 'document')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25A2.25 2.25 0 0 0 6 4.5v15A2.25 2.25 0 0 0 8.25 21h7.5A2.25 2.25 0 0 0 18 18.75V18m-7.5-6h7.5m-7.5 3h7.5" /></svg>
                                    @elseif ($feature['icon'] === 'chart')
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                    @else
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                                    @endif
                                </span>
                                <h3 class="mt-5 text-base font-semibold text-white">{{ $feature['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-400">{{ $feature['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="cara-kerja" class="relative py-20 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-emerald-300">Cara kerja</p>
                            <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Tiga langkah, absensi selesai.</h2>
                            <p class="mt-4 text-base leading-7 text-slate-400">Tidak perlu antre lama atau mencatat manual. Wajah menjadi identitas kehadiran Anda.</p>

                            <div class="mt-8 space-y-4">
                                @foreach ([
                                    ['step' => '01', 'title' => 'Daftarkan wajah', 'description' => 'Siswa dan guru mendaftarkan wajah sekali melalui menu Face ID.'],
                                    ['step' => '02', 'title' => 'Pindai di kamera', 'description' => 'Buka halaman absensi, hadapkan wajah, dan sistem mengenali Anda secara otomatis.'],
                                    ['step' => '03', 'title' => 'Pantau hasilnya', 'description' => 'Kehadiran langsung masuk ke dashboard, laporan, dan notifikasi wali murid.'],
                                ] as $item)
                                    <div class="flex gap-4 rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-sm font-semibold text-emerald-300">{{ $item['step'] }}</span>
                                        <div>
                                            <h3 class="text-sm font-semibold text-white">{{ $item['title'] }}</h3>
                                            <p class="mt-1 text-sm leading-6 text-slate-400">{{ $item['description'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-[2rem] border border-white/10 bg-white/[0.03] p-6 sm:p-8">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-white">Kehadiran Hari Ini</p>
                                    <p class="mt-1 text-xs text-slate-500">Ringkasan seluruh kelas</p>
                                </div>
                                <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-300">Live</span>
                            </div>

                            <div class="mt-6 grid grid-cols-2 gap-3">
                                @foreach ([
                                    ['label' => 'Hadir', 'value' => '228', 'color' => 'bg-emerald-400'],
                                    ['label' => 'Terlambat', 'value' => '12', 'color' => 'bg-amber-400'],
                                    ['label' => 'Izin / Sakit', 'value' => '8', 'color' => 'bg-sky-400'],
                                    ['label' => 'Belum Absen', 'value' => '4', 'color' => 'bg-rose-400'],
                                ] as $stat)
                                    <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                                        <div class="flex items-center gap-2 text-xs text-slate-400">
                                            <span class="h-2 w-2 rounded-full {{ $stat['color'] }}"></span>
                                            {{ $stat['label'] }}
                                        </div>
                                        <p class="mt-2 text-2xl font-bold text-white">{{ $stat['value'] }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-5 space-y-3">
                                @foreach ([
                                    ['name' => 'Siswa Contoh', 'class' => 'X IPA 1', 'status' => 'Hadir', 'time' => '07:02'],
                                    ['name' => 'Siti Aminah', 'class' => 'X IPA 1', 'status' => 'Terlambat', 'time' => '07:28'],
                                    ['name' => 'Budi Santoso', 'class' => 'X IPA 2', 'status' => 'Izin', 'time' => '07:15'],
                                ] as $row)
                                    <div class="flex items-center justify-between rounded-xl border border-white/5 bg-slate-900/50 px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-500/15 text-xs font-semibold text-indigo-200">{{ mb_substr($row['name'], 0, 1) }}</span>
                                            <div>
                                                <p class="text-sm font-medium text-white">{{ $row['name'] }}</p>
                                                <p class="text-xs text-slate-500">{{ $row['class'] }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs font-medium {{ $row['status'] === 'Terlambat' ? 'text-amber-300' : ($row['status'] === 'Izin' ? 'text-sky-300' : 'text-emerald-300') }}">{{ $row['status'] }}</p>
                                            <p class="text-xs text-slate-500">{{ $row['time'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="relative pb-20 sm:pb-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="relative overflow-hidden rounded-[2rem] border border-indigo-400/20 bg-gradient-to-br from-indigo-500/20 via-slate-900 to-slate-950 px-6 py-12 text-center sm:px-12">
                        <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
                        <h2 class="relative text-3xl font-bold tracking-tight sm:text-4xl">Siap membuat absensi lebih efisien?</h2>
                        <p class="relative mx-auto mt-4 max-w-2xl text-base leading-7 text-slate-300">Mulai dari satu pemindaian wajah. Sistem akan mencatat, merekap, dan memberi tahu wali murid secara otomatis.</p>
                        <div class="relative mt-8 flex flex-wrap justify-center gap-3">
                            <a href="{{ route('attendance.scan') }}" class="rounded-2xl bg-white px-6 py-3.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">Absen Sekarang</a>
                            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="rounded-2xl border border-white/20 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">{{ auth()->check() ? 'Buka Dashboard' : 'Masuk ke Akun' }}</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-white/10 py-8">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 text-sm text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                <p>&copy; {{ now()->year }} {{ config('app.name', 'FaceID Absen') }}. Sistem absensi sekolah.</p>
                <div class="flex items-center gap-5">
                    <a href="{{ route('attendance.scan') }}" class="transition hover:text-slate-300">Absensi</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="transition hover:text-slate-300">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="transition hover:text-slate-300">Masuk</a>
                    @endauth
                </div>
            </div>
        </footer>
    </body>
</html>
