<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Face ID') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Data Wajah</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Wajah digunakan untuk absensi mandiri dan login tanpa memasukkan email atau password.
                        </p>
                    </div>
                    <div>
                        @if ($user->hasRegisteredFace())
                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700">
                                Sudah terdaftar
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700">
                                Belum terdaftar
                            </span>
                        @endif
                    </div>
                </div>

                @if ($user->face_registered_at)
                    <p class="mt-3 text-sm text-gray-500">
                        Terakhir diperbarui: {{ $user->face_registered_at->format('d M Y H:i') }}
                    </p>
                @endif
            </div>

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div class="mx-auto max-w-xl">
                    <div class="relative overflow-hidden rounded-xl border-4 border-indigo-500 bg-black">
                        <video id="video" autoplay muted playsinline class="aspect-[4/3] w-full -scale-x-100 object-cover"></video>
                        <div class="absolute inset-x-0 top-0 flex justify-center p-3">
                            <span id="camera-badge" class="rounded-full bg-black/60 px-3 py-1 text-xs text-white">Menyiapkan kamera...</span>
                        </div>
                    </div>

                    <div id="status-box" class="mt-4 rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                        <div id="status-title" class="font-semibold text-indigo-800">Memuat sistem pengenalan wajah...</div>
                        <div id="status-detail" class="mt-1 text-sm text-indigo-700">Mohon tunggu sebentar.</div>
                    </div>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <button
                            id="capture-button"
                            type="button"
                            disabled
                            class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Ambil & Simpan Wajah
                        </button>

                        <a
                            href="{{ route('profile.edit') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            Kembali ke Profil
                        </a>
                    </div>

                    <p class="mt-4 text-xs text-gray-500">
                        Saat pendaftaran, sistem mengambil 3 sampel wajah. Pastikan wajah terlihat jelas, pencahayaan cukup, dan tanpa masker.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1/dist/face-api.min.js"></script>
    <script>
        const MODEL_URL = @json($modelUrl);
        const STORE_URL = @json(route('profile.face.store'));
        const PROFILE_URL = @json(route('profile.face.edit'));
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const video = document.getElementById('video');
        const captureButton = document.getElementById('capture-button');
        const statusBox = document.getElementById('status-box');
        const statusTitle = document.getElementById('status-title');
        const statusDetail = document.getElementById('status-detail');
        const cameraBadge = document.getElementById('camera-badge');

        const detectorOptions = new faceapi.TinyFaceDetectorOptions({
            inputSize: 320,
            scoreThreshold: 0.5,
        });

        let modelsReady = false;
        let capturing = false;

        function setStatus(type, title, detail) {
            const styles = {
                info: 'border-indigo-200 bg-indigo-50',
                success: 'border-emerald-200 bg-emerald-50',
                warning: 'border-amber-200 bg-amber-50',
                error: 'border-rose-200 bg-rose-50',
            };

            const textStyles = {
                info: 'text-indigo-800',
                success: 'text-emerald-800',
                warning: 'text-amber-800',
                error: 'text-rose-800',
            };

            statusBox.className = 'mt-4 rounded-xl border p-4 ' + styles[type];
            statusTitle.className = 'font-semibold ' + textStyles[type];
            statusDetail.className = 'mt-1 text-sm ' + textStyles[type];
            statusTitle.textContent = title;
            statusDetail.textContent = detail || '';
        }

        async function loadModels() {
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
                captureButton.disabled = false;
                setStatus('info', 'Siap mendaftarkan wajah', 'Klik tombol di bawah untuk mengambil sampel wajah.');
            } catch (error) {
                cameraBadge.textContent = 'Kamera tidak aktif';
                cameraBadge.className = 'rounded-full bg-rose-500/80 px-3 py-1 text-xs text-white';
                setStatus('error', 'Kamera tidak dapat dibuka', 'Izinkan akses kamera pada browser, lalu muat ulang halaman.');
            }
        }

        function delay(milliseconds) {
            return new Promise((resolve) => setTimeout(resolve, milliseconds));
        }

        async function captureSamples(target) {
            const samples = [];
            let attempts = 0;
            const maxAttempts = target * 8;

            while (samples.length < target && attempts < maxAttempts) {
                attempts++;
                setStatus(
                    'info',
                    `Mengambil sampel ${samples.length + 1} dari ${target}`,
                    'Hadapkan wajah lurus ke kamera dan tahan posisi sebentar.'
                );

                const detection = await faceapi
                    .detectSingleFace(video, detectorOptions)
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (detection) {
                    samples.push(Array.from(detection.descriptor));
                }

                await delay(700);
            }

            return samples;
        }

        async function saveFace() {
            if (!modelsReady || capturing) {
                return;
            }

            capturing = true;
            captureButton.disabled = true;
            captureButton.textContent = 'Memproses...';

            try {
                const samples = await captureSamples(3);

                if (samples.length < 3) {
                    setStatus('error', 'Wajah tidak terdeteksi', 'Pastikan wajah terlihat jelas di kamera, lalu coba lagi.');
                    return;
                }

                const response = await fetch(STORE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({ descriptors: samples }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                    setStatus('error', 'Gagal menyimpan wajah', firstError || data.message || 'Terjadi kesalahan saat menyimpan data wajah.');
                    return;
                }

                setStatus('success', 'Wajah berhasil didaftarkan', data.message || 'Anda sekarang dapat absen dan login menggunakan wajah.');

                setTimeout(() => {
                    window.location.href = PROFILE_URL;
                }, 1400);
            } catch (error) {
                setStatus('error', 'Koneksi gagal', 'Tidak dapat menghubungi server. Periksa koneksi lalu coba lagi.');
            } finally {
                capturing = false;
                captureButton.disabled = false;
                captureButton.textContent = 'Ambil & Simpan Wajah';
            }
        }

        captureButton.addEventListener('click', saveFace);

        window.addEventListener('load', () => {
            loadModels().catch(() => {
                setStatus('error', 'Gagal memuat model', 'Periksa koneksi internet lalu muat ulang halaman.');
            });
        });
    </script>
</x-app-layout>
