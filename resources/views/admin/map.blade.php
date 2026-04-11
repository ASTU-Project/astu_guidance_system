@extends('layouts.admin')

@section('title', 'Map Managment')
@section('page-title', 'Map Managment')

@section('content')
    <div class="space-y-5">
        @if(session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-medium">Please fix the following:</p>
                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-md border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Map Management </h3>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        id="open-map-preview"
                        class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-500"
                    >
                        <i class="fa fa-eye text-[11px]"></i>
                        Preview
                    </button>
                    <button
                        type="button"
                        onclick="document.getElementById('map-location-modal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                    >
                        <i class="fa fa-plus text-[11px]"></i>
                        Add Location
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px]">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-left">
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Icon</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Name</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Description</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Latitude</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Longitude</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Category</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Action</th>

                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($locations as $location)
                            <tr>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <i class="{{ $location->icon ?: 'fa fa-map-marker-alt' }} text-cyan-700"></i>
                                </td>  
                                <td class="px-5 py-4 text-sm font-bold text-slate-600">{{$location->name}}</td>
                                <td class="px-5 py-4 text-sm text-slate-600 ">{{$location->description}}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{$location->latitude}}</td>  
                                <td class="px-5 py-4 text-sm text-slate-600">{{$location->longitude}}</td>  
                                <td class="px-5 py-4 text-sm text-slate-600">{{$location->category}}</td>  
                                <td class="px-5 py-4 text-sm text-slate-600 gap-3 flex">
                                    <button
                                        type="button"
                                        class="text-green-600"
                                        data-edit-map
                                        data-id="{{ $location->id }}"
                                        data-name="{{ $location->name }}"
                                        data-description="{{ $location->description }}"
                                        data-latitude="{{ $location->latitude }}"
                                        data-longitude="{{ $location->longitude }}"
                                        data-category="{{ $location->category }}"
                                        data-icon="{{ $location->icon ?: 'fa fa-map-marker-alt' }}"
                                    >
                                        <i class="fa fa-edit"></i>    
                                    </button>
                                    <form action="{{ route('admin.map.destroy', $location->id) }}" method="POST" onsubmit="return confirm('Delete this location?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td> 
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">
                                    No map locations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
            </div>
        <div id="map-location-modal" class="{{ $errors->any() || old('name') || old('latitude') ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-slate-950/50" onclick="document.getElementById('map-location-modal').classList.add('hidden')"></div>
            <div class="relative w-full max-w-lg rounded-md bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">Add Map Location</h3>
                    </div>
                    <button type="button" onclick="document.getElementById('map-location-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('admin.map.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label for="location-name" class="mb-1 block text-sm font-medium text-slate-700">Location Name</label>
                        <input id="location-name" type="text" name="name" value="{{ old('name') }}" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" placeholder="e.g. Main Library">
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="location-latitude" class="mb-1 block text-sm font-medium text-slate-700">Latitude</label>
                            <input id="location-latitude" type="number" step="0.0000001" name="latitude" value="{{ old('latitude') }}" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" placeholder="9.0320000">
                        </div>
                        <div>
                            <label for="location-longitude" class="mb-1 block text-sm font-medium text-slate-700">Longitude</label>
                            <input id="location-longitude" type="number" step="0.0000001" name="longitude" value="{{ old('longitude') }}" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" placeholder="38.7630000">
                        </div>
                    </div>

                    <div>
                        <label for="location-description" class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                        <textarea id="location-description" name="description" rows="3" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" placeholder="Short description of this place">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="location-category" class="mb-1 block text-sm font-medium text-slate-700">Category</label>
                            <select id="location-category" name="category" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                                <option value="">Select category</option>
                                <option value="Cafe" @selected(old('category') === 'Cafe')>Cafe</option>
                                <option value="Library" @selected(old('category') === 'Library')>Library</option>
                                <option value="Classroom" @selected(old('category') === 'Classroom')>Classroom</option>
                                <option value="Office" @selected(old('category') === 'Office')>Office</option>
                                <option value="Lab" @selected(old('category') === 'Lab')>Lab</option>
                            </select>
                        </div>
                        <div>
                            <label for="location-icon" class="mb-1 block text-sm font-medium text-slate-700">Icon</label>
                            <select id="location-icon" name="icon" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                                <option value="fa fa-map-marker-alt" @selected(old('icon', 'fa fa-map-marker-alt') === 'fa fa-map-marker-alt')>Map Pin</option>
                                <option value="fa fa-building" @selected(old('icon') === 'fa fa-building')>Building</option>
                                <option value="fa fa-book" @selected(old('icon') === 'fa fa-book')>Library</option>
                                <option value="fa fa-coffee" @selected(old('icon') === 'fa fa-coffee')>Cafe</option>
                                <option value="fa fa-flask" @selected(old('icon') === 'fa fa-flask')>Lab</option>
                                <option value="fa fa-graduation-cap" @selected(old('icon') === 'fa fa-graduation-cap')>Classroom</option>
                                <option value="fa fa-hospital" @selected(old('icon') === 'fa fa-hospital')>Clinic</option>
                                <option value="fa fa-bus" @selected(old('icon') === 'fa fa-bus')>Transport</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('map-location-modal').classList.add('hidden')" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                            Save Location
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="map-location-update-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-slate-950/50" onclick="document.getElementById('map-location-update-modal').classList.add('hidden')"></div>
            <div class="relative w-full max-w-lg rounded-md bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">Update Map Location</h3>
                    </div>
                    <button type="button" onclick="document.getElementById('map-location-update-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <form
                    id="map-location-update-form"
                    action="{{ route('admin.map.update', ['location' => '__ID__']) }}"
                    data-action-template="{{ route('admin.map.update', ['location' => '__ID__']) }}"
                    method="POST"
                    class="mt-5 space-y-4"
                >
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="update-location-name" class="mb-1 block text-sm font-medium text-slate-700">Location Name</label>
                        <input id="update-location-name" name="name" type="text" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" placeholder="e.g. Main Library">
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="update-location-latitude" class="mb-1 block text-sm font-medium text-slate-700">Latitude</label>
                            <input id="update-location-latitude" name="latitude" type="number" step="0.0000001" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" placeholder="9.0320000">
                        </div>
                        <div>
                            <label for="update-location-longitude" class="mb-1 block text-sm font-medium text-slate-700">Longitude</label>
                            <input id="update-location-longitude" name="longitude" type="number" step="0.0000001" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" placeholder="38.7630000">
                        </div>
                    </div>

                    <div>
                        <label for="update-location-description" class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                        <textarea id="update-location-description" name="description" rows="3" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" placeholder="Short description of this place"></textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="update-location-category" class="mb-1 block text-sm font-medium text-slate-700">Category</label>
                            <select id="update-location-category" name="category" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                                <option value="">Select category</option>
                                <option value="Cafe">Cafe</option>
                                <option value="Library">Library</option>
                                <option value="Classroom">Classroom</option>
                                <option value="Office">Office</option>
                                <option value="Lab">Lab</option>
                            </select>
                        </div>
                        <div>
                            <label for="update-location-icon" class="mb-1 block text-sm font-medium text-slate-700">Icon</label>
                            <select id="update-location-icon" name="icon" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                                <option value="fa fa-map-marker-alt">Map Pin</option>
                                <option value="fa fa-building">Building</option>
                                <option value="fa fa-book">Library</option>
                                <option value="fa fa-coffee">Cafe</option>
                                <option value="fa fa-flask">Lab</option>
                                <option value="fa fa-graduation-cap">Classroom</option>
                                <option value="fa fa-hospital">Clinic</option>
                                <option value="fa fa-bus">Transport</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" onclick="document.getElementById('map-location-update-modal').classList.add('hidden')" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                            Update Location
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="campus-map-preview-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-slate-950/60" onclick="document.getElementById('campus-map-preview-modal').classList.add('hidden')"></div>
            <div class="relative w-full max-w-6xl rounded-md bg-white p-4 shadow-2xl">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Campus Map Preview</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-700" onclick="document.getElementById('campus-map-preview-modal').classList.add('hidden')">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div id="campus-preview-map" class="h-[65vh] w-full rounded-md border border-slate-200"></div>
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
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @php
        $campusLocations = $locations->map(function ($location) {
            return [
                'name' => $location->name,
                'description' => $location->description,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'category' => $location->category,
            ];
        })->values();
    @endphp
    <script>
        const campusLocations = @json($campusLocations);

        let previewMap = null;
        let previewLayer = null;

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('map-location-modal');
                if (modal) {
                    modal.classList.add('hidden');
                }

                const updateModal = document.getElementById('map-location-update-modal');
                if (updateModal) {
                    updateModal.classList.add('hidden');
                }

                const previewModal = document.getElementById('campus-map-preview-modal');
                if (previewModal) {
                    previewModal.classList.add('hidden');
                }
            }
        });

        document.querySelectorAll('[data-edit-map]').forEach(function (button) {
            button.addEventListener('click', function () {
                const updateForm = document.getElementById('map-location-update-form');
                if (updateForm) {
                    const template = updateForm.dataset.actionTemplate || '';
                    updateForm.action = template.replace('__ID__', this.dataset.id || '');
                }

                document.getElementById('update-location-name').value = this.dataset.name || '';
                document.getElementById('update-location-description').value = this.dataset.description || '';
                document.getElementById('update-location-latitude').value = this.dataset.latitude || '';
                document.getElementById('update-location-longitude').value = this.dataset.longitude || '';
                document.getElementById('update-location-category').value = this.dataset.category || '';
                document.getElementById('update-location-icon').value = this.dataset.icon || 'fa fa-map-marker-alt';

                document.getElementById('map-location-update-modal').classList.remove('hidden');
            });
        });

        function initPreviewMap() {
            const mapContainer = document.getElementById('campus-preview-map');
            if (!mapContainer) {
                return;
            }

            if (!previewMap) {
                previewMap = L.map('campus-preview-map', {
                    zoomControl: true,
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(previewMap);

                previewLayer = L.layerGroup().addTo(previewMap);
            }

            previewLayer.clearLayers();

            if (campusLocations.length > 0) {
                const bounds = [];

                campusLocations.forEach(function (location) {
                    const latLng = [location.latitude, location.longitude];
                    bounds.push(latLng);

                    L.marker(latLng)
                        .bindPopup('<strong>' + location.name + '</strong><br>' + (location.description || 'No description'))
                        .addTo(previewLayer);
                });

                previewMap.fitBounds(bounds, { padding: [30, 30], maxZoom: 17 });
            } else {
                previewMap.setView([8.562296, 39.294502], 15);
            }

            setTimeout(function () {
                previewMap.invalidateSize();
            }, 80);
        }

        const previewButton = document.getElementById('open-map-preview');
        if (previewButton) {
            previewButton.addEventListener('click', function () {
                document.getElementById('campus-map-preview-modal').classList.remove('hidden');
                initPreviewMap();
            });
        }
    </script>
@endpush