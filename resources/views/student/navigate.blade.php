@extends('layouts.student')

@section('title', 'Navigate')
@section('page-title', 'Navigate')

@section('content')
    <div class="space-y-5">
        <div class="rounded-md border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Find any location quickly</h3>
                </div>

                <div class="flex w-full max-w-xl items-center gap-2">
                    <div class="relative w-full">
                        <i class="fa fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input
                            id="location-search"
                            type="text"
                            placeholder="Search by name, category, or description"
                            class="w-full rounded-md border border-slate-300 py-2 pl-9 pr-3 text-sm text-slate-700 outline-none focus:border-slate-400"
                        >
                    </div>
                    <button
                        type="button"
                        id="clear-location-search"
                        class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <div class="grid gap-0 xl:grid-cols-[minmax(0,1.65fr)_minmax(0,0.85fr)]">
                <div class="border-b border-slate-100 p-4 xl:border-b-0 xl:border-r">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="text-sm font-medium text-slate-700">Map view</div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                id="student-toggle-satellite"
                                class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                            >
                                Satellite
                            </button>
                            <button
                                type="button"
                                id="student-map-fullscreen"
                                class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                            >
                                Fullscreen
                            </button>
                        </div>
                    </div>
                    <div id="student-campus-map" class="h-[65vh] w-full rounded-md border border-slate-200"></div>
                </div>

                <div class="min-w-0 bg-slate-50/60">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <h4 class="text-sm font-semibold text-slate-900">Listed Locations</h4>
                        <span id="location-count" class="rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600">0</span>
                    </div>
                    <div id="location-results" class="max-h-[65vh] overflow-y-auto divide-y divide-slate-100 bg-white"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-popup-content-wrapper {
            border-radius: 10px;
        }

        .location-item-active {
            background: rgb(241 245 249);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @php
        $campusLocations = $locations->map(function ($location) {
            return [
                'id' => $location->id,
                'name' => $location->name,
                'description' => $location->description,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'category' => $location->category,
                'icon' => $location->icon ?: 'fa fa-map-marker-alt',
            ];
        })->values();
    @endphp
    <script>
        const allLocations = @json($campusLocations);

        let studentMap = null;
        let studentMarkerLayer = null;
        let studentDefaultTiles = null;
        let studentSatelliteTiles = null;
        let studentSatelliteEnabled = false;
        let activeMarkerById = {};
        let filteredLocations = [...allLocations];

        function buildStudentMap() {
            if (studentMap) {
                return;
            }

            studentMap = L.map('student-campus-map', {
                zoomControl: true,
            });

            studentDefaultTiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            });

            studentSatelliteTiles = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri',
            });

            studentDefaultTiles.addTo(studentMap);
            studentMarkerLayer = L.layerGroup().addTo(studentMap);
        }

        function markerPopupHtml(location) {
            return `
                <div class="space-y-1">
                    <div class="font-semibold text-slate-800">${location.name}</div>
                    <div class="text-xs text-slate-600">${location.category || 'Uncategorized'}</div>
                    <div class="text-xs text-slate-500">${location.description || 'No description available.'}</div>
                </div>
            `;
        }

        function renderLocationResults(items) {
            const results = document.getElementById('location-results');
            const count = document.getElementById('location-count');

            if (!results || !count) {
                return;
            }

            count.textContent = String(items.length);

            if (items.length === 0) {
                results.innerHTML = '<div class="px-4 py-6 text-sm text-slate-500">No matching locations found.</div>';
                return;
            }

            results.innerHTML = items.map((location) => {
                return `
                    <button
                        type="button"
                        data-location-id="${location.id}"
                        class="location-result-item flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-slate-50"
                    >
                        <div class="mt-0.5 flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-[11px] text-slate-600">
                            <i class="${location.icon || 'fa fa-map-marker-alt'}"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-slate-900">${location.name}</div>
                            <div class="mt-1 text-xs text-slate-500">${location.category || 'Uncategorized'}</div>
                            <div class="mt-1 text-xs text-slate-500">${location.description || 'No description available.'}</div>
                        </div>
                    </button>
                `;
            }).join('');

            results.querySelectorAll('[data-location-id]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = Number(button.getAttribute('data-location-id'));
                    focusLocation(id);
                });
            });
        }

        function renderMarkers(items) {
            if (!studentMap || !studentMarkerLayer) {
                return;
            }

            studentMarkerLayer.clearLayers();
            activeMarkerById = {};

            if (items.length === 0) {
                studentMap.setView([8.562296, 39.294502], 15);
                return;
            }

            const bounds = [];

            items.forEach((location) => {
                const latLng = [location.latitude, location.longitude];
                bounds.push(latLng);

                const marker = L.marker(latLng)
                    .bindPopup(markerPopupHtml(location))
                    .addTo(studentMarkerLayer);

                activeMarkerById[String(location.id)] = marker;
            });

            studentMap.fitBounds(bounds, { padding: [24, 24], maxZoom: 17 });
        }

        function filterLocations(searchText) {
            const keyword = (searchText || '').trim().toLowerCase();

            if (!keyword) {
                return [...allLocations];
            }

            return allLocations.filter((location) => {
                const searchable = [location.name, location.category, location.description]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();

                return searchable.includes(keyword);
            });
        }

        function refreshNavigation(searchText = '') {
            filteredLocations = filterLocations(searchText);
            renderLocationResults(filteredLocations);
            renderMarkers(filteredLocations);
        }

        function focusLocation(id) {
            const marker = activeMarkerById[String(id)];
            const location = filteredLocations.find((item) => Number(item.id) === Number(id));

            if (!marker || !location || !studentMap) {
                return;
            }

            studentMap.setView([location.latitude, location.longitude], Math.max(studentMap.getZoom(), 17));
            marker.openPopup();

            document.querySelectorAll('.location-result-item').forEach((item) => {
                item.classList.remove('location-item-active');
            });

            const selectedRow = document.querySelector(`.location-result-item[data-location-id="${id}"]`);
            if (selectedRow) {
                selectedRow.classList.add('location-item-active');
            }
        }

        function toggleStudentSatellite() {
            const button = document.getElementById('student-toggle-satellite');

            if (!studentMap || !studentDefaultTiles || !studentSatelliteTiles || !button) {
                return;
            }

            studentSatelliteEnabled = !studentSatelliteEnabled;

            if (studentSatelliteEnabled) {
                studentMap.removeLayer(studentDefaultTiles);
                studentSatelliteTiles.addTo(studentMap);
                button.textContent = 'Map';
            } else {
                studentMap.removeLayer(studentSatelliteTiles);
                studentDefaultTiles.addTo(studentMap);
                button.textContent = 'Satellite';
            }
        }

        function openStudentFullscreen() {
            const mapContainer = document.getElementById('student-campus-map');

            if (!mapContainer) {
                return;
            }

            if (document.fullscreenElement) {
                document.exitFullscreen();
                return;
            }

            if (mapContainer.requestFullscreen) {
                mapContainer.requestFullscreen();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            buildStudentMap();
            refreshNavigation('');

            const searchInput = document.getElementById('location-search');
            const clearButton = document.getElementById('clear-location-search');
            const satelliteButton = document.getElementById('student-toggle-satellite');
            const fullscreenButton = document.getElementById('student-map-fullscreen');

            if (searchInput) {
                searchInput.addEventListener('input', (event) => {
                    refreshNavigation(event.target.value || '');
                });
            }

            if (clearButton && searchInput) {
                clearButton.addEventListener('click', () => {
                    searchInput.value = '';
                    refreshNavigation('');
                });
            }

            if (satelliteButton) {
                satelliteButton.addEventListener('click', toggleStudentSatellite);
            }

            if (fullscreenButton) {
                fullscreenButton.addEventListener('click', openStudentFullscreen);
            }

            document.addEventListener('fullscreenchange', () => {
                setTimeout(() => {
                    if (studentMap) {
                        studentMap.invalidateSize();
                    }
                }, 80);
            });
        });
    </script>
@endpush
