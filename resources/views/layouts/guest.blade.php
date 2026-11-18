<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FaceID Absen') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <aside class="relative hidden overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(99,102,241,0.25),transparent_45%),radial-gradient(circle_at_bottom_left,rgba(14,165,233,0.15),transparent_40%)]"></div>
                <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(rgba(148,163,184,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,0.06)_1px,transparent_1px)] bg-[length:56px_56px] [mask-image:linear-gradient(to_bottom,black,transparent_90%)]"></div>

                <a href="/" class="relative flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-500/15 text-indigo-300 ring-1 ring-inset ring-indigo-400/30">
                        <x-application-logo class="h-7 w-7" />
                    </span>
                    <span class="text-base font-semibold tracking-tight">{{ config('app.name', 'FaceID Absen') }}</span>
                </a>

                <div class="relative max-w-md">
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-300">Absensi sekolah</p>
                    <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight">Satu pemindaian wajah untuk kehadiran yang lebih rapi.</h1>
                    <p class="mt-5 text-base leading-7 text-slate-400">Masuk untuk memantau kehadiran, mengelola izin, dan melihat laporan sekolah dalam satu dashboard.</p>

                    <div class="mt-8 space-y-3">
                        @foreach ([
                            'Check-in dan check-out dengan pengenalan wajah',
                            'Rekap harian dan bulanan otomatis',
                            'Notifikasi keterlambatan untuk wali murid',
                        ] as $item)
                            <div class="flex items-start gap-3 text-sm text-slate-300">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-300">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                </span>
                                {{ $item }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <p class="relative text-xs text-slate-500">&copy; {{ now()->year }} {{ config('app.name', 'FaceID Absen') }}. Sistem absensi sekolah.</p>
            </aside>

            <main class="flex min-h-screen flex-col justify-center bg-white px-6 py-10 sm:px-10">
                <div class="mx-auto w-full max-w-md">
                    <a href="/" class="mb-8 flex items-center gap-2.5 lg:hidden">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100">
                            <x-application-logo class="h-6 w-6" />
                        </span>
                        <span class="text-sm font-semibold tracking-tight text-slate-900">{{ config('app.name', 'FaceID Absen') }}</span>
                    </a>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        {{ $slot }}
                    </div>

                    <a href="/" class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-slate-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                        Kembali ke beranda
                    </a>
                </div>
            </main>
        </div>
    </body>
</html>
