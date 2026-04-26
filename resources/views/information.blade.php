<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASTU Management System</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .hero-mesh {
            position: absolute;
            inset: -20% -10%;
            background:
                radial-gradient(circle at 20% 25%, rgba(6, 182, 212, 0.28), transparent 34%),
                radial-gradient(circle at 78% 30%, rgba(14, 165, 233, 0.24), transparent 32%),
                radial-gradient(circle at 30% 78%, rgba(132, 204, 22, 0.18), transparent 30%),
                radial-gradient(circle at 72% 76%, rgba(51, 65, 85, 0.14), transparent 30%);
            filter: blur(26px);
            animation: meshDrift 18s ease-in-out infinite alternate;
            pointer-events: none;
        }

        .hero-grid-pattern {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(to right, rgba(148, 163, 184, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(148, 163, 184, 0.1) 1px, transparent 1px);
            background-size: 34px 34px;
            mask-image: radial-gradient(circle at 50% 45%, rgba(0, 0, 0, 0.85), transparent 75%);
            pointer-events: none;
        }

        .float-card {
            animation: floatCard 6.5s ease-in-out infinite;
        }

        .float-card.delay-1 {
            animation-delay: 1.1s;
        }

        .float-card.delay-2 {
            animation-delay: 2.2s;
        }

        .testimonial-track {
            display: flex;
            width: max-content;
            animation: marqueeX 36s linear infinite;
        }

        .testimonial-marquee {
            mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
            -webkit-mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
        }

        .testimonial-marquee:hover .testimonial-track {
            animation-play-state: paused;
        }

        .cta-noise {
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(rgba(255, 255, 255, 0.2) 0.7px, transparent 0.7px),
                radial-gradient(rgba(15, 23, 42, 0.12) 0.7px, transparent 0.7px);
            background-size: 16px 16px, 20px 20px;
            background-position: 0 0, 10px 10px;
            opacity: 0.5;
            pointer-events: none;
        }

        @keyframes meshDrift {
            0% {
                transform: translate3d(-3%, -2%, 0) scale(1);
            }
            100% {
                transform: translate3d(3%, 2%, 0) scale(1.05);
            }
        }

        @keyframes floatCard {
            0%,
            100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes marqueeX {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 sm:px-8">
            <a href="/" class="text-lg font-bold tracking-tight text-slate-950">ASTUMG</a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                <a href="#features" class="transition hover:text-slate-950">Features</a>
                <a href="/information" class="transition hover:text-slate-950">Community</a>
                <a href="#map" class="transition hover:text-slate-950">Map</a>
                <a href="#faq" class="transition hover:text-slate-950">FAQ</a>
                <a href="/login" class="transition hover:text-slate-950">Login</a>
            </nav>
            <a href="/login" class="rounded-md bg-slate-950 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Login</a>
        </div>
    </header>

    <main>
        {{-- // content here --}}
    </main>

    <footer class="bg-white/90 py-10">
        <div class="mx-auto max-w-7xl px-6 sm:px-8">
            <div class="grid gap-10 lg:grid-cols-3">
                <div>
                    <p class="text-lg font-semibold text-slate-950">ASTUMG</p>
                    <p class="mt-3 max-w-sm text-sm leading-6 text-slate-600">A modern campus management experience with public pages, secure login, and a clear student-first journey.</p>
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-800">Explore</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600">
                        <li><a href="#features" class="transition hover:text-slate-950">Features</a></li>
                        <li><a href="/information" class="transition hover:text-slate-950">Community</a></li>
                        <li><a href="#map" class="transition hover:text-slate-950">Map</a></li>
                        <li><a href="#faq" class="transition hover:text-slate-950">FAQ</a></li>
                        <li><a href="/login" class="transition hover:text-slate-950">Login</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 text-sm text-slate-500">© {{ date('Y') }} ASTU Guidance System.</div>
        </div>
    </footer>

</body>
</html>
