<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ match ($mode) { 'login' => 'Login Wajah', 'checkout' => 'Absensi Pulang', default => 'Absensi Wajah' } }} - {{ config('app.name', 'FaceID Absen') }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1/dist/face-api.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <header class="border-b border-white/10 bg-slate-900/80">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
            <a href="{{ url('/') }}" class="text-lg font-semibold tracking-tight">FaceID Absen</a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('attendance.scan') }}" class="text-slate-300 hover:text-white">Absen Masuk</a>
                <a href="{{ route('attendance.checkout') }}" class="text-slate-300 hover:text-white">Absen Pulang</a>
                <a href="{{ route('face.login') }}" class="text-slate-300 hover:text-white">Login Wajah</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-white px-4 py-2 font-medium text-slate-900">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg border border-white/20 px-4 py-2 font-medium text-white">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto flex max-w-3xl flex-col items-center px-4 py-10">
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-bold">{{ match ($mode) { 'login' => 'Login dengan Wajah', 'checkout' => 'Absensi Pulang', default => 'Absensi Mandiri' } }}</h1>
            <p class="mt-2 max-w-xl text-slate-400">
                {{ match ($mode) {
                    'login' => 'Posisikan wajah Anda di depan kamera. Sistem akan mengenali wajah, mencatat absensi masuk, lalu mengarahkan Anda ke dashboard.',
                    'checkout' => 'Posisikan wajah Anda di depan kamera untuk mencatat jam pulang. Pastikan Anda sudah absen masuk hari ini.',
                    default => 'Posisikan wajah Anda di depan kamera. Absensi masuk akan otomatis tercatat tanpa perlu login.',
                } }}
            </p>
        </div>

        <div class="relative w-full max-w-xl overflow-hidden rounded-2xl border-4 border-indigo-500 bg-black shadow-2xl">
            <video id="video" autoplay muted playsinline class="aspect-[4/3] w-full -scale-x-100 object-cover"></video>
            <div class="absolute inset-x-0 top-0 flex justify-center p-3">
                <span id="camera-badge" class="rounded-full bg-black/60 px-3 py-1 text-xs text-slate-200">Menyiapkan kamera...</span>
            </div>
        </div>

        <div id="status-box" class="mt-6 w-full max-w-xl rounded-2xl border border-white/10 bg-slate-900 p-5">
            <div id="status-title" class="text-lg font-semibold">Memuat sistem pengenalan wajah...</div>
            <div id="status-detail" class="mt-1 text-sm text-slate-400">Mohon tunggu sebentar.</div>
        </div>

        <div class="mt-6 grid w-full max-w-xl gap-3 text-sm text-slate-400 sm:grid-cols-3">
            <div class="rounded-xl border border-white/10 bg-slate-900/60 p-3">Pastikan pencahayaan cukup.</div>
            <div class="rounded-xl border border-white/10 bg-slate-900/60 p-3">Hadapkan wajah lurus ke kamera.</div>
            <div class="rounded-xl border border-white/10 bg-slate-900/60 p-3">Lepas masker atau penutup wajah.</div>
        </div>

        @guest
            <p class="mt-6 text-sm text-slate-400">
                Belum mendaftarkan wajah?
                <a href="{{ route('login') }}" class="font-medium text-indigo-300 hover:text-indigo-200">Login</a>
                lalu buka menu <span class="text-slate-200">Face ID</span>.
            </p>
        @endguest
    </main>

    <script>
        const MODE = @json($mode);
        const MODEL_URL = @json($modelUrl);
        const RECOGNIZE_URL = @json(route('attendance.recognize'));
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const video = document.getElementById('video');
        const statusBox = document.getElementById('status-box');
        const statusTitle = document.getElementById('status-title');
        const statusDetail = document.getElementById('status-detail');
        const cameraBadge = document.getElementById('camera-badge');

        let modelsReady = false;
        let processing = false;
        let cooldownUntil = 0;
        let lastSignature = null;
        let sameSignatureCount = 0;

        const detectorOptions = new faceapi.TinyFaceDetectorOptions({
            inputSize: 320,
            scoreThreshold: 0.5,
        });

        function setStatus(type, title, detail) {
            statusTitle.textContent = title;
            statusDetail.textContent = detail || '';

            statusBox.className = 'mt-6 w-full max-w-xl rounded-2xl border p-5 ' + {
                info: 'border-indigo-400/30 bg-indigo-500/10',
                success: 'border-emerald-400/30 bg-emerald-500/10',
                warning: 'border-amber-400/30 bg-amber-500/10',
                error: 'border-rose-400/30 bg-rose-500/10',
            }[type];

            statusTitle.className = 'text-lg font-semibold ' + {
                info: 'text-indigo-200',
                success: 'text-emerald-200',
                warning: 'text-amber-200',
                error: 'text-rose-200',
            }[type];
        }

        async function loadModels() {
            setStatus('info', 'Memuat sistem pengenalan wajah...', 'Menyiapkan model AI. Proses ini hanya sekali saat halaman dibuka.');

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);

            modelsReady = true;
            await startVideo();
        }

        async function startVideo() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                    },
                    audio: false,
                });

                video.srcObject = stream;
                await video.play();

                cameraBadge.textContent = 'Kamera aktif';
                cameraBadge.className = 'rounded-full bg-emerald-500/80 px-3 py-1 text-xs text-white';
                setStatus('info', 'Siap memindai wajah', 'Hadapkan wajah Anda ke kamera dan tunggu hingga proses selesai.');
                scanLoop();
            } catch (error) {
                cameraBadge.textContent = 'Kamera tidak aktif';
                cameraBadge.className = 'rounded-full bg-rose-500/80 px-3 py-1 text-xs text-white';
                setStatus('error', 'Kamera tidak dapat dibuka', 'Izinkan akses kamera pada browser, lalu muat ulang halaman.');
            }
        }

        async function scanLoop() {
            if (!modelsReady) {
                return;
            }

            if (processing || Date.now() < cooldownUntil) {
                setTimeout(scanLoop, 700);
                return;
            }

            try {
                const detection = await faceapi
                    .detectSingleFace(video, detectorOptions)
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    lastSignature = null;
                    sameSignatureCount = 0;
                    setStatus('info', 'Mencari wajah...', 'Pastikan wajah terlihat jelas di dalam kamera.');
                    setTimeout(scanLoop, 800);
                    return;
                }

                const descriptor = Array.from(detection.descriptor);
                const signature = descriptor.slice(0, 12).map((value) => value.toFixed(3)).join(',');

                if (signature === lastSignature) {
                    sameSignatureCount++;
                } else {
                    lastSignature = signature;
                    sameSignatureCount = 1;
                }

                if (sameSignatureCount < 2) {
                    setStatus('info', 'Wajah terdeteksi', 'Tahan posisi sebentar agar wajah dapat diverifikasi.');
                    setTimeout(scanLoop, 600);
                    return;
                }

                processing = true;
                const succeeded = await submitAttendance(descriptor);
                processing = false;
                cooldownUntil = Date.now() + (succeeded ? 8000 : 3500);
            } catch (error) {
                setStatus('error', 'Terjadi kesalahan', 'Sistem gagal memproses gambar. Coba muat ulang halaman.');
            }

            setTimeout(scanLoop, 900);
        }

        async function submitAttendance(descriptor) {
            try {
                const response = await fetch(RECOGNIZE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({
                        mode: MODE,
                        descriptors: [descriptor],
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const message = data.message
                        || (response.status === 419
                            ? 'Sesi halaman sudah berakhir. Muat ulang halaman lalu coba lagi.'
                            : 'Data wajah tidak dapat diproses.');

                    setStatus('error', 'Gagal memproses wajah', message);
                    return false;
                }

                if (data.status === 'error') {
                    setStatus('error', 'Gagal memproses absensi', data.message || 'Wajah belum terdaftar.');
                    return false;
                }

                setStatus(
                    data.status === 'warning' ? 'warning' : 'success',
                    `${data.user.name} - ${data.user.role_label}`,
                    data.message
                );

                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1600);
                }

                return true;
            } catch (error) {
                setStatus('error', 'Koneksi gagal', 'Tidak dapat menghubungi server. Periksa koneksi lalu coba lagi.');
                return false;
            }
        }

        window.addEventListener('load', () => {
            loadModels().catch(() => {
                setStatus('error', 'Gagal memuat model', 'Periksa koneksi internet lalu muat ulang halaman.');
            });
        });
    </script>
</body>
</html>
