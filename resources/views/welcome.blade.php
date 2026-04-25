<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASTU Management System</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-popup-content-wrapper {
            border-radius: 5px;
            padding: 0;
        }
        .leaflet-popup-content {
            margin: 0;
            width: 280px !important;
        }
        .map-place-popup {
            overflow: hidden;
            border-radius: 5px;
            background: #fff;
        }
        .map-place-popup__image {
            height: 128px;
            width: 100%;
            object-fit: cover;
            display: block;
            background: #e2e8f0;
        }
        .map-place-popup__body {
            padding: 10px 12px 12px;
        }
        .map-place-popup__title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }
        .map-place-popup__category {
            display: inline-block;
            margin-top: 6px;
            border-radius: 9999px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            color: #0e7490;
            background: #ecfeff;
            border: 1px solid #a5f3fc;
        }
        .map-place-popup__description {
            margin-top: 8px;
            font-size: 12px;
            color: #475569;
            line-height: 1.4;
        }
        .map-place-popup__coords {
            margin-top: 8px;
            font-size: 11px;
            color: #64748b;
        }
        .location-item-active {
            background: rgb(241 245 249);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 sm:px-8">
            <a href="/" class="text-lg font-bold tracking-tight text-slate-950">ASTUMG</a>
            <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                <a href="#features" class="transition hover:text-slate-950">Features</a>
                <a href="#community" class="transition hover:text-slate-950">Community</a>
                <a href="#map" class="transition hover:text-slate-950">Map</a>
                <a href="#faq" class="transition hover:text-slate-950">FAQ</a>
                <a href="/login" class="transition hover:text-slate-950">Login</a>
            </nav>
            <a href="/login" class="rounded-md bg-slate-950 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Login</a>
        </div>
    </header>

    <main>
        <section class="relative isolate overflow-hidden bg-white">
            <div class="hero-mesh"></div>
            <div class="hero-grid-pattern"></div>
            <div class="relative mx-auto max-w-7xl px-6 py-20 sm:px-8 lg:px-10 lg:py-24">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="inline-flex rounded-full border border-slate-300/80 bg-white/85 px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-slate-700">
                        ASTU University Management
                    </span>
                    <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                        A smarter campus flow from timetable to clubs, all in one place.
                    </h1>
                    <p class="mx-auto mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                        Designed for students and staff to manage academics, discover locations, join communities, and stay updated with events using one connected dashboard.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                        <a href="/login" class="inline-flex items-center justify-center rounded-md bg-slate-950 px-7 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Start Now</a>
                        <a href="#features" class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white/80 px-7 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">Explore Features</a>
                    </div>
                </div>

                <div class="pointer-events-none relative mx-auto mt-14 hidden h-72 max-w-5xl lg:block">
                    <article class="float-card absolute left-[2%] top-14 w-64 -rotate-6 rounded-xl border border-slate-200 bg-white/90 p-4 shadow-xl shadow-slate-900/10 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Student Dashboard</p>
                        <h3 class="mt-3 text-lg font-bold text-slate-900">GPA Snapshot</h3>
                        <div class="mt-4 h-2 rounded-full bg-slate-100">
                            <div class="h-2 w-4/5 rounded-full bg-cyan-500"></div>
                        </div>
                        <div class="mt-3 text-xs text-slate-500">Semester progress: 82%</div>
                    </article>

                    <article class="float-card delay-1 absolute left-[36%] top-2 w-72 rotate-2 rounded-xl border border-slate-200 bg-white/92 p-4 shadow-2xl shadow-slate-900/10 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Calendar</p>
                        <h3 class="mt-3 text-lg font-bold text-slate-900">Weekly Schedule</h3>
                        <div class="mt-3 grid grid-cols-7 gap-1">
                            <div class="h-10 rounded bg-slate-100"></div>
                            <div class="h-10 rounded bg-cyan-200"></div>
                            <div class="h-10 rounded bg-slate-100"></div>
                            <div class="h-10 rounded bg-lime-200"></div>
                            <div class="h-10 rounded bg-slate-100"></div>
                            <div class="h-10 rounded bg-slate-100"></div>
                            <div class="h-10 rounded bg-slate-100"></div>
                        </div>
                    </article>

                    <article class="float-card delay-2 absolute right-[2%] top-16 w-64 rotate-[7deg] rounded-xl border border-slate-200 bg-white/90 p-4 shadow-xl shadow-slate-900/10 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Campus Map</p>
                        <h3 class="mt-3 text-lg font-bold text-slate-900">Navigation Card</h3>
                        <div class="mt-3 h-24 rounded-lg bg-gradient-to-br from-slate-100 via-cyan-100 to-sky-200"></div>
                        <p class="mt-3 text-xs text-slate-500">Library to Main Hall - 7 min walk</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="features" class="mx-auto max-w-7xl px-6 py-20 sm:px-8 lg:px-10">
            <div class="mb-10 max-w-2xl">
                <span class="inline-flex rounded-full border border-slate-300 bg-slate-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-slate-700">Platform Features</span>
                <h2 class="mt-5 text-3xl font-semibold text-slate-950 sm:text-4xl">Asymmetric bento layout for the tools students use daily.</h2>
            </div>
            <div class="grid auto-rows-[160px] gap-4 md:grid-cols-6">
                <article class="relative col-span-6 row-span-2 overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:col-span-4">
                    <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-cyan-100"></div>
                    <p class="relative text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">AI Assistant</p>
                    <h3 class="relative mt-3 text-2xl font-bold text-slate-950">Ask anything about campus, courses, and deadlines.</h3>
                    <p class="relative mt-3 max-w-xl text-sm leading-7 text-slate-600">Context-aware support helps new and returning students make decisions faster with less stress.</p>
                </article>
                <article class="col-span-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Navigation</p>
                    <h3 class="mt-3 text-lg font-bold text-slate-950">Campus map with location search</h3>
                    <p class="mt-2 text-sm text-slate-600">Find buildings, departments, and services quickly.</p>
                </article>
                <article class="col-span-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Academic Status</p>
                    <h3 class="mt-3 text-lg font-bold text-slate-950">Track grade and GPA trajectory</h3>
                    <p class="mt-2 text-sm text-slate-600">View performance insights in a clear interface.</p>
                </article>
                <article class="col-span-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:col-span-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Calendar + Events</p>
                    <h3 class="mt-3 text-xl font-bold text-slate-950">Recurring classes and one-time events in one timeline.</h3>
                    <p class="mt-2 text-sm text-slate-600">Never miss schedules, clubs, announcements, or policy reminders.</p>
                </article>
            </div>
        </section>

        <section id="community" class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-6 sm:px-8 lg:px-10">
                <div class="mb-8 max-w-2xl">
                    <span class="inline-flex rounded-full border border-slate-300 bg-slate-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-slate-700">Community and Clubs</span>
                    <h2 class="mt-5 text-3xl font-semibold text-slate-950">One stat per card, built for quick scanning.</h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-3xl font-extrabold tracking-tight text-slate-950">42</p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Active Clubs</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-3xl font-extrabold tracking-tight text-slate-950">8.4K</p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Members Joined</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-3xl font-extrabold tracking-tight text-slate-950">126</p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Events This Term</p>
                    </article>
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-3xl font-extrabold tracking-tight text-slate-950">91%</p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Student Engagement</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-6 py-20 sm:px-8 lg:px-10">
            <div class="mb-8 max-w-2xl">
                <span class="inline-flex rounded-full border border-slate-300 bg-slate-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-slate-700">Testimonials</span>
                <h2 class="mt-5 text-3xl font-semibold text-slate-950">Students and staff feedback, continuously moving.</h2>
            </div>

            <div class="testimonial-marquee overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="testimonial-track gap-4">
                    @php
                        $testimonials = [
                            ['name' => 'Meron G.', 'role' => '3rd Year Student', 'quote' => 'The timetable and map together made my week less chaotic from day one.'],
                            ['name' => 'Abel D.', 'role' => 'Department Staff', 'quote' => 'Announcements now reach students clearly, and event tracking is simpler.'],
                            ['name' => 'Rahel T.', 'role' => 'Club Leader', 'quote' => 'Club discoverability improved a lot. New members join faster every semester.'],
                            ['name' => 'Yohannes K.', 'role' => '2nd Year Student', 'quote' => 'The interface is clean and easy to use even on my phone between classes.'],
                            ['name' => 'Hana B.', 'role' => 'Faculty Assistant', 'quote' => 'Everything feels organized: policies, schedules, and student communication.'],
                        ];
                        $marqueeTestimonials = array_merge($testimonials, $testimonials);
                    @endphp

                    @foreach($marqueeTestimonials as $entry)
                        <article class="w-[300px] shrink-0 rounded-xl border border-slate-200 bg-slate-50 p-5">
                            <p class="text-sm leading-7 text-slate-700">"{{ $entry['quote'] }}"</p>
                            <div class="mt-4 border-t border-slate-200 pt-3">
                                <p class="text-sm font-semibold text-slate-900">{{ $entry['name'] }}</p>
                                <p class="text-xs uppercase tracking-[0.14em] text-slate-500">{{ $entry['role'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="map" class="bg-white py-20">
            <div class="mx-auto max-w-3xl px-2 sm:px-4 md:px-6 lg:px-10">
                <div id="welcome-campus-map" class="relative z-0 h-60 sm:h-80 md:h-[22rem] lg:h-[24rem] w-full overflow-hidden rounded-xl border border-slate-200"></div>
            </div>
        </section>

        <section id="faq" class="mx-auto max-w-7xl px-6 py-20 sm:px-8 lg:px-10">
            <div class="mb-8 max-w-3xl">
                <span class="inline-flex rounded-full border border-slate-300 bg-slate-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-slate-700">FAQ</span>
                <h2 class="mt-5 text-3xl font-semibold text-slate-950">Search and filter common questions instantly.</h2>
                <div class="relative mt-6">
                    <input id="faq-search" type="text" placeholder="Search question or keyword" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400" />
                </div>
            </div>

            <div id="faq-list" class="grid gap-3">
                @php
                    $faqItems = [
                        ['q' => 'How do I log in to the student dashboard?', 'a' => 'Use your university credentials from the Login button at the top right.'],
                        ['q' => 'Can I track my semester GPA and cumulative GPA?', 'a' => 'Yes. Academic status pages show GPA trends and related performance details.'],
                        ['q' => 'How do I find a building on campus?', 'a' => 'Open the Navigate section to search places by name, category, or description.'],
                        ['q' => 'How can I join clubs and communities?', 'a' => 'Go to the Community area to browse groups, view events, and register interest.'],
                        ['q' => 'Does the calendar support recurring weekly events?', 'a' => 'Yes. You can create recurring weekly events or single date-specific events.'],
                    ];
                @endphp

                @foreach($faqItems as $item)
                    <article class="faq-item rounded-xl border border-slate-200 bg-white p-5" data-question="{{ strtolower($item['q']) }} {{ strtolower($item['a']) }}">
                        <h3 class="text-base font-semibold text-slate-900">{{ $item['q'] }}</h3>
                        <p class="mt-2 text-sm leading-7 text-slate-600">{{ $item['a'] }}</p>
                    </article>
                @endforeach

                <div id="faq-empty" class="hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                    No FAQ matches your search.
                </div>
            </div>
        </section>

        <section class="pb-20">
            <div class="mx-auto max-w-7xl px-6 sm:px-8 lg:px-10">
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-900 to-slate-700 px-6 py-12 text-center text-white sm:px-12">
                    <div class="cta-noise"></div>
                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-200">Ready to Join</p>
                        <h2 class="mx-auto mt-4 max-w-3xl text-3xl font-semibold sm:text-4xl">Start your ASTU digital campus journey today.</h2>
                        <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-200">Secure access, clear navigation, connected student tools, and faster communication in one platform.</p>
                        <div class="mt-8">
                            <a href="/login" class="inline-flex items-center justify-center rounded-md bg-white px-7 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">Login to Continue</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    // Map logic
    const welcomeLocations = @json($locations);
    let welcomeMap = null;
    let welcomeMarkerLayer = null;

    function buildWelcomeMap() {
        if (welcomeMap) return;
        welcomeMap = L.map('welcome-campus-map', { zoomControl: true });
        const defaultTiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 20, subdomains: 'abcd', attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        });
        defaultTiles.addTo(welcomeMap);
        welcomeMarkerLayer = L.layerGroup().addTo(welcomeMap);
    }

    function welcomeMarkerPopupHtml(location) {
        const category = location.category || 'Campus Place';
        const description = location.description || 'No description available.';
        const imageUrl = String(location.image_url || '').trim() || 'https://picsum.photos/seed/campus-default/480/260';
        return `
            <div class="map-place-popup">
                <img class="map-place-popup__image" src="${imageUrl}" alt="${location.name}" loading="lazy">
                <div class="map-place-popup__body">
                    <h4 class="map-place-popup__title">${location.name}</h4>
                    <span class="map-place-popup__category">${category}</span>
                    <div class="map-place-popup__description">${description}</div>
                    <div class="map-place-popup__coords">${Number(location.latitude).toFixed(5)}, ${Number(location.longitude).toFixed(5)}</div>
                </div>
            </div>
        `;
    }

    function welcomeRenderMarkers(items) {
        if (!welcomeMap || !welcomeMarkerLayer) return;
        welcomeMarkerLayer.clearLayers();
        if (!items.length) {
            welcomeMap.setView([8.562296, 39.294502], 15);
            return;
        }
        const bounds = [];
        items.forEach((location) => {
            const latLng = [location.latitude, location.longitude];
            bounds.push(latLng);
            L.marker(latLng).bindPopup(welcomeMarkerPopupHtml(location)).addTo(welcomeMarkerLayer);
        });
        welcomeMap.fitBounds(bounds, { padding: [24, 24], maxZoom: 17 });
    }

    // FAQ search logic
    function setupFaqSearch() {
        const searchInput = document.getElementById('faq-search');
        const items = Array.from(document.querySelectorAll('.faq-item'));
        const emptyState = document.getElementById('faq-empty');
        if (!searchInput || items.length === 0) return;
        searchInput.addEventListener('input', (event) => {
            const term = String(event.target.value || '').trim().toLowerCase();
            let visibleCount = 0;
            items.forEach((item) => {
                const haystack = item.dataset.question || '';
                const isVisible = term === '' || haystack.includes(term);
                item.classList.toggle('hidden', !isVisible);
                if (isVisible) visibleCount += 1;
            });
            if (emptyState) emptyState.classList.toggle('hidden', visibleCount !== 0);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        buildWelcomeMap();
        welcomeRenderMarkers(welcomeLocations);
        setupFaqSearch();
    });
    // --- End combined script ---
    </script>

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
                        <li><a href="#community" class="transition hover:text-slate-950">Community</a></li>
                        <li><a href="#map" class="transition hover:text-slate-950">Map</a></li>
                        <li><a href="#faq" class="transition hover:text-slate-950">FAQ</a></li>
                        <li><a href="/login" class="transition hover:text-slate-950">Login</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 text-sm text-slate-500">� {{ date('Y') }} ASTU Guidance System.</div>
        </div>
    </footer>


</body>
</html>
