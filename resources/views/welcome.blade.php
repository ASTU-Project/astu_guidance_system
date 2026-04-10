<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASTU Management System</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 text-slate-900">
    <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 sm:px-8">
            <a href="/" class="text-lg font-bold tracking-tight text-slate-950">ASTUMG</a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                <a href="#about" class="transition hover:text-slate-950">About</a>
                <a href="#map" class="transition hover:text-slate-950">Map</a>
                <a href="/login" class="transition hover:text-slate-950">Login</a>
            </nav>
            <a href="/login" class="rounded-md bg-slate-950 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Login</a>
        </div>
    </header>

    <main>
        <section class="bg-white">
            <div class="mx-auto max-w-7xl px-6 py-20 sm:px-8 lg:px-10">
                <div class="grid gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                    <div class="space-y-6">
                        <span class="inline-flex rounded-full bg-slate-100 px-4 py-1 text-sm font-semibold uppercase tracking-[0.32em] text-slate-700">
                            ASTU University Management
                        </span>
                        <h1 class="text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl">
                            One place for your student life, campus navigation, and academic progress.
                        </h1>
                        <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                            ASTU Management System gives students and staff a clear entry into campus tools, GPA tracking, calendar events, maps, and intelligent support.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="/login" class="inline-flex items-center justify-center rounded-md bg-slate-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Login</a>
                            <a href="#about" class="inline-flex items-center justify-center rounded-md bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-200">Learn more</a>
                        </div>
                    </div>
                    <div class="rounded-md bg-slate-950 p-8 text-white shadow-xl shadow-slate-900/5">
                        <div class="rounded-[1.75rem] bg-slate-900 p-8">
                            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-300">Student dashboard</p>
                            <h2 class="mt-6 text-3xl font-semibold leading-tight">Track your GPA, events, and campus life in one dashboard.</h2>
                            <p class="mt-4 text-slate-300 leading-7">
                                A friendly digital home for every ASTU student. Navigate campus locations, review your academic progress, and stay on top of deadlines.
                            </p>
                        </div>
                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-md bg-slate-800 p-4">
                                <p class="text-sm uppercase tracking-[0.24em] text-slate-400">Students</p>
                                <p class="mt-3 text-3xl font-semibold">18K+</p>
                            </div>
                            <div class="rounded-md bg-slate-800 p-4">
                                <p class="text-sm uppercase tracking-[0.24em] text-slate-400">Events</p>
                                <p class="mt-3 text-3xl font-semibold">120+</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="mx-auto max-w-7xl px-6 py-20 sm:px-8 lg:px-10">
            <div class="grid gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                <div class="rounded-md bg-white p-10 shadow-xl shadow-slate-900/5">
                    <span class="inline-flex rounded-full bg-slate-100 px-4 py-1 text-sm font-semibold text-slate-900">About ASTU Management</span>
                    <h2 class="mt-6 text-3xl font-semibold text-slate-950">A calm, human-first portal for your campus life.</h2>
                    <p class="mt-5 text-base leading-8 text-slate-600">
                        This landing page is a static showcase of the system experience. Later it will connect students with GPA tracking, campus maps, course guidance, and the admin dashboard.
                    </p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-md bg-slate-100 p-5">
                            <h3 class="font-semibold text-slate-950">Student first</h3>
                            <p class="mt-2 text-sm text-slate-600">Simple access to your academic profile, map, and important campus links.</p>
                        </div>
                        <div class="rounded-md bg-slate-100 p-5">
                            <h3 class="font-semibold text-slate-950">Clean design</h3>
                            <p class="mt-2 text-sm text-slate-600">A calm and readable layout without unnecessary decorations.</p>
                        </div>
                    </div>
                </div>
                <div class="overflow-hidden rounded-md bg-slate-950 shadow-xl shadow-slate-900/5">
                    <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1200&q=80" alt="Students working on a campus project" class="h-full w-full object-cover" />
                </div>
            </div>
        </section>

        <section id="map" class="bg-white py-20">
            <div class="mx-auto max-w-7xl px-6 sm:px-8 lg:px-10">
                <div class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                    <div>
                        <span class="inline-flex rounded-full bg-slate-100 px-4 py-1 text-sm font-semibold uppercase tracking-[0.32em] text-slate-700">Campus map</span>
                        <h2 class="mt-6 text-3xl font-semibold text-slate-950">Find your way around ASTU.</h2>
                        <p class="mt-5 max-w-xl text-base leading-8 text-slate-600">
                            A simple map preview helps everyone discover buildings, libraries, and campus services. This is a static placeholder that can be replaced with an interactive map later.
                        </p>
                    </div>
                    <div class="overflow-hidden rounded-md bg-slate-950">
                        <iframe title="ASTU map preview" class="h-96 w-full border-0" loading="lazy" src="https://maps.google.com/maps?q=adama%20science%20and%20technology%20university&t=&z=14&ie=UTF8&iwloc=&output=embed"></iframe>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-white/90 py-10">
        <div class="mx-auto max-w-7xl px-6 sm:px-8">
            <div class="grid gap-10 lg:grid-cols-3">
                <div>
                    <p class="text-lg font-semibold text-slate-950">ASTUMG</p>
                    <p class="mt-3 max-w-sm text-sm leading-6 text-slate-600">A modern campus management experience with public pages, secure login, and a clear visitor journey.</p>
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-800">Explore</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600">
                        <li><a href="#about" class="transition hover:text-slate-950">About</a></li>
                        <li><a href="#map" class="transition hover:text-slate-950">Map</a></li>
                        <li><a href="/login" class="transition hover:text-slate-950">Login</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 text-sm text-slate-500">© {{ date('Y') }} ASTU Management System.</div>
        </div>
    </footer>
</body>
</html>
