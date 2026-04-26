<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASTU Management System</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
                <a href="/#features" class="transition hover:text-slate-950">Features</a>
                <a href="/information" class="transition hover:text-slate-950">Community</a>
                <a href="/#map" class="transition hover:text-slate-950">Map</a>
                <a href="/#faq" class="transition hover:text-slate-950">FAQ</a>
                <a href="/login" class="transition hover:text-slate-950">Login</a>
            </nav>
            <a href="/login"
                class="rounded-md bg-slate-950 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Login</a>
        </div>
    </header>

    <main>
        <section class="relative isolate overflow-hidden bg-white">
            <div class="hero-mesh"></div>
            <div class="hero-grid-pattern"></div>
            <div class="relative mx-auto max-w-7xl px-6 py-16 sm:px-8 lg:px-10 lg:py-24">
                <div class="mx-auto max-w-3xl text-center">
                    <span
                        class="inline-flex rounded-full border border-slate-300/80 bg-white/85 px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-slate-700">
                        ASTU Community Hub
                    </span>
                    <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl lg:text-6xl">
                        Connect, collaborate, and grow with peers.
                    </h1>
                    <p class="mx-auto mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                        Explore student-led clubs and official university channels to stay updated and engaged with
                        campus life.
                    </p>
                </div>
            </div>
        </section>

        <!-- Channels Section -->
        <section id="channels-section" class="mx-auto max-w-7xl px-6 py-20 sm:px-8 lg:px-10">
            <div class="mb-12">
                <span
                    class="inline-flex rounded-full border border-slate-300 bg-slate-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-slate-700">Official
                    Channels</span>
                <h2 class="mt-5 text-3xl font-semibold text-slate-950">Stay connected via Telegram</h2>
                <p class="mt-2 text-base leading-7 text-slate-600">Join our official channels for real-time updates
                    and announcements.</p>
            </div>

            <div id="channels-grid" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <!-- Channels will be injected here -->
            </div>

            <div id="channels-load-more-container" class="mt-12 flex justify-center">
                <button id="btn-load-channels"
                    class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-7 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
                    Load More Channels
                </button>
            </div>
        </section>

        <!-- Clubs Section -->
        <section id="clubs-section" class="bg-white py-20">
            <div class="mx-auto max-w-7xl px-6 sm:px-8 lg:px-10">
                <div class="mb-12">
                    <span
                        class="inline-flex rounded-full border border-slate-300 bg-slate-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-slate-700">Student
                        Clubs</span>
                    <h2 class="mt-5 text-3xl font-semibold text-slate-950">Discover your passions</h2>
                    <p class="mt-2 text-base leading-7 text-slate-600">Join academic, creative, and social clubs
                        managed by student leaders.</p>
                </div>

                <div id="clubs-grid" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <!-- Clubs will be injected here -->
                </div>

                <div id="clubs-load-more-container" class="mt-12 flex justify-center">
                    <button id="btn-load-clubs"
                        class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-7 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
                        Load More Clubs
                    </button>
                </div>
            </div>
        </section>

        <section class="pb-20">
            <div class="mx-auto max-w-7xl px-6 sm:px-8 lg:px-10">
                <div
                    class="relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-900 to-slate-700 px-6 py-12 text-center text-white sm:px-12">
                    <div class="cta-noise"></div>
                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-200">Get Involved</p>
                        <h2 class="mx-auto mt-4 max-w-3xl text-3xl font-semibold sm:text-4xl">Ready to make an
                            impact?</h2>
                        <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-200">Start by joining a
                            channel or club that interests you. Your journey starts here.</p>
                        <div class="mt-8">
                            <a href="/login"
                                class="inline-flex items-center justify-center rounded-md bg-white px-7 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">Contact
                                Community Admin</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const allCommunities = @json($communities);
            const staticChannels = allCommunities.filter(c => c.type === 'telegram');
            const staticClubs = allCommunities.filter(c => c.type === 'club');

            let channelsVisible = 0;
            let clubsVisible = 0;
            const INCREMENT = 5;

            function createCard(item, isClub = false) {
                const div = document.createElement('div');
                div.className = 'bg-white border border-slate-200 rounded-md p-6 flex flex-col gap-1 cursor-pointer transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:border-slate-300 shadow-sm';
                div.onclick = () => openCommunityModal(item);

                const logoSrc = item.logo_url ? `/storage/${item.logo_url}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(item.name)}&background=f1f5f9&color=1f2937&bold=true&size=56`;
                
                div.innerHTML = `
                    <div class="flex flex-col items-start">
                        <img src="${logoSrc}"
                             alt="${item.name}"
                             class="w-14 h-14 rounded-full border-2 border-gray-800 object-cover flex-shrink-0 shadow-sm ${isClub ? 'mb-1' : ''}">
                        <h2 class="text-lg font-bold text-gray-900">${item.name}</h2>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">${item.description.length > 120 ? item.description.substring(0, 120) + '...' : item.description}</p>
                `;
                return div;
            }

            function loadChannels() {
                const grid = document.getElementById('channels-grid');
                const nextBatch = staticChannels.slice(channelsVisible, channelsVisible + INCREMENT);
                nextBatch.forEach(channel => {
                    grid.appendChild(createCard(channel));
                });
                channelsVisible += nextBatch.length;
                
                if (channelsVisible >= staticChannels.length) {
                    document.getElementById('channels-load-more-container').classList.add('hidden');
                }
            }

            function loadClubs() {
                const grid = document.getElementById('clubs-grid');
                const nextBatch = staticClubs.slice(clubsVisible, clubsVisible + INCREMENT);
                nextBatch.forEach(club => {
                    grid.appendChild(createCard(club, true));
                });
                clubsVisible += nextBatch.length;
                
                if (clubsVisible >= staticClubs.length) {
                    document.getElementById('clubs-load-more-container').classList.add('hidden');
                }
            }

            const btnLoadChannels = document.getElementById('btn-load-channels');
            const btnLoadClubs = document.getElementById('btn-load-clubs');

            if (btnLoadChannels) btnLoadChannels.addEventListener('click', loadChannels);
            if (btnLoadClubs) btnLoadClubs.addEventListener('click', loadClubs);

            // Initial Load
            loadChannels();
            loadClubs();
        });

        // Modal Functions
        let currentCommunityUrl = '';
        function openCommunityModal(data) {
            const overlay = document.getElementById('modalOverlay');
            document.getElementById('modalTitle').textContent = data.name;
            document.getElementById('modalBadge').textContent = data.type ? data.type.charAt(0).toUpperCase() + data.type.slice(1) : '';
            document.getElementById('modalCategory').textContent = data.category || 'General';
            document.getElementById('modalDescription').textContent = data.description || '';
            
            const isClub = data.type === 'club';
            document.getElementById('modalAdminLabel').textContent = isClub ? 'President' : 'Admin';
            document.getElementById('modalLinkLabel').textContent = isClub ? 'Visit Club' : 'Visit Channel';
            
            const header = document.getElementById('modalHeader');
            if (data.image_url) {
                header.style.backgroundImage = `url('/storage/${data.image_url}')`;
            } else {
                header.style.backgroundImage = 'none';
                header.style.backgroundColor = '#f1f5f9';
            }

            const logo = document.getElementById('modalLogo');
            if (data.logo_url) {
                logo.src = `/storage/${data.logo_url}`;
                logo.style.display = '';
            } else {
                logo.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.name)}&background=f1f5f9&color=1f2937&bold=true&size=56`;
                logo.style.display = '';
            }

            document.getElementById('modalLeaderValue').textContent = data.leader || '—';
            document.getElementById('modalStatusValue').textContent = data.is_active ? 'Active' : 'Inactive';
            
            currentCommunityUrl = data.url || '#';
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const overlay = document.getElementById('modalOverlay');
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function closeModalOnOverlay(e) {
            if (e.target === e.currentTarget) closeModal();
        }

        function visitLink() {
            if (currentCommunityUrl && currentCommunityUrl !== '#') {
                window.open(currentCommunityUrl, '_blank');
            } else {
                alert('No link available.');
            }
        }

        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
    </script>

    <!-- Modal HTML -->
    <div id="modalOverlay"
         onclick="closeModalOnOverlay(event)"
         class="hidden fixed inset-0 bg-black/50 z-[9999] items-center justify-center p-5 backdrop-blur-sm">
        <div onclick="event.stopPropagation()"
             class="bg-white rounded-md w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col shadow-2xl modal-animate">

            <!-- Modal Header -->
            <div id="modalHeader" class="relative h-44 flex-shrink-0 bg-cover bg-center">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/80"></div>
                <button onclick="closeModal()"
                        class="absolute top-4 right-4 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center text-white text-xl transition z-10">
                    &times;
                </button>
                <div class="absolute left-6 bottom-6 flex flex-col items-start">
                    <img id="modalLogo" src="" alt="Logo" class="w-16 h-16 rounded-full border-2 border-white shadow-lg mb-2 object-cover bg-white">
                    <h2 id="modalTitle" class="text-2xl font-bold text-white [text-shadow:0_2px_4px_rgba(0,0,0,0.2)] mb-1"></h2>
                    <span id="modalBadge" class="inline-block bg-violet-600 text-white text-xs font-semibold px-3 py-1 rounded-full mt-1"></span>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-content p-6 overflow-y-auto flex-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Description</p>
                <p id="modalDescription" class="text-sm text-gray-700 leading-relaxed mb-6"></p>
                <div class="info-row flex justify-between items-center py-3 border-b border-slate-200">
                    <span id="modalAdminLabel" class="info-label text-sm text-gray-500">Admin</span>
                    <span id="modalLeaderValue" class="info-value text-sm font-semibold text-gray-900">—</span>
                </div>
                <div class="info-row flex justify-between items-center py-3 border-b border-slate-200">
                    <span class="info-label text-sm text-gray-500">Category</span>
                    <span id="modalCategory" class="info-value text-sm font-semibold text-gray-900">General</span>
                </div>
                <div class="info-row flex justify-between items-center py-3 border-b border-slate-200">
                    <span class="info-label text-sm text-gray-500">Status</span>
                    <span id="modalStatusValue" class="info-value text-sm font-semibold text-emerald-600">Active</span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex-shrink-0">
                <button onclick="visitLink()"
                        class="w-full flex items-center justify-center gap-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold py-3 rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    <span id="modalLinkLabel">Visit</span>
                </button>
            </div>
        </div>
    </div>

    <style>
        .modal-animate { animation: modalIn 0.25s ease-out; }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>

</html>