<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>QR Code Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-cyan-50 text-slate-900">
    <div class="relative mx-auto flex min-h-screen max-w-6xl items-center justify-center px-4 py-14 sm:px-6 lg:px-10">
        <div class="pointer-events-none absolute -top-8 -left-4 h-36 w-36 rounded-full bg-cyan-200/40 blur-2xl"></div>
        <div class="pointer-events-none absolute -right-10 -bottom-10 h-44 w-44 rounded-full bg-slate-300/35 blur-2xl"></div>

        <div class="relative w-full max-w-md rounded-md border border-slate-200/80 bg-white/95 p-8 shadow-2xl shadow-slate-200/70 backdrop-blur sm:p-10">
            <div class="space-y-3 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">Smart Login</p>
                <h1 class="text-3xl font-black text-slate-950 sm:text-4xl">Smart Login</h1>
            </div>

            <div id="error" class="mt-6 hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"></div>

            <div class="mt-6 space-y-4">
                <div id="reader" class="mx-auto w-full max-w-sm overflow-hidden rounded-lg border-2 border-cyan-500 bg-black"></div>

                <div id="authSuccess" class="hidden rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    Login successful! Redirecting...
                </div>
                <div id="authFailure" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                    Auth failed.
                </div>

                <div class="flex gap-3">
                    <button id="startBtn" class="w-full rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                        Start Camera
                    </button>
                    <button id="stopBtn" class="hidden w-full rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2">
                        Stop
                    </button>
                </div>
            </div>

            <p class="mt-6 text-center text-sm text-slate-600">
                Prefer password login?
                <a href="{{ route('student.login') }}" class="font-semibold text-cyan-700 hover:text-cyan-800">Use ID login</a>
            </p>
        </div>
    </div>

    <script>
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const readerEl = document.getElementById('reader');
        const errorDiv = document.getElementById('error');
        const authSuccess = document.getElementById('authSuccess');
        const authFailure = document.getElementById('authFailure');
        let scanner = null;

        function hideResults() {
            authSuccess.classList.add('hidden');
            authFailure.classList.add('hidden');
            errorDiv.classList.add('hidden');
        }

        function showError(msg) {
            errorDiv.textContent = msg;
            errorDiv.classList.remove('hidden');
        }

        async function startCamera() {
            hideResults();
            startBtn.disabled = true;
            startBtn.textContent = '⏳ Starting...';
            try {
                scanner = new Html5Qrcode("reader");
                await scanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 220, height: 220 }, formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE] },
                    (decodedText) => {
                        scanner.stop().catch(() => {});
                        startBtn.classList.remove('hidden');
                        stopBtn.classList.add('hidden');
                        // Send code to server for auth
                        fetch("/qr-login", {
                            method: "POST",
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ code: decodedText.trim() })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                authSuccess.classList.remove('hidden');
                                setTimeout(() => { window.location.href = data.redirect; }, 1500);
                            } else {
                                authFailure.classList.remove('hidden');
                            }
                        })
                        .catch(() => { showError('Server error.'); });
                    },
                    () => {}
                );
                startBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');
            } catch (err) {
                showError('Camera failed to start.');
            } finally {
                startBtn.disabled = false;
                startBtn.textContent = 'Start Camera';
            }
        }

        startBtn.addEventListener('click', startCamera);
        stopBtn.addEventListener('click', async () => {
            if (scanner) { try { await scanner.stop(); scanner.clear(); } catch(e) {} }
            startBtn.classList.remove('hidden');
            stopBtn.classList.add('hidden');
            hideResults();
        });
    </script>
</body>
</html>
