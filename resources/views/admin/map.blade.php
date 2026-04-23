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
                    <h3 class="text-lg font-semibold text-slate-950">Map Management ({{ $locations->count() }})</h3>
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
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Image</th>
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
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    @if($location->image_url)
                                        <img
                                            src="{{ $location->image_url }}"
                                            alt="{{ $location->name }}"
                                            class="h-10 w-14 rounded-md object-cover border border-slate-200"
                                            loading="lazy"
                                        >
                                    @else
                                        <span class="text-xs text-slate-400">No image</span>
                                    @endif
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
                                        data-image-url="{{ $location->image_url }}"
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
                                <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-400">
                                    No map locations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        <div id="map-location-modal" class="{{ $errors->any() || old('name') || old('latitude') || old('image') ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center px-4">
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

                <form action="{{ route('admin.map.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
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

                    <div>
                        <label for="location-image" class="mb-1 block text-sm font-medium text-slate-700">Location Image</label>
                        <input id="location-image" type="file" name="image" accept="image/png,image/jpeg,image/webp" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus:border-slate-400 focus:outline-none">
                        <p class="mt-1 text-[11px] text-slate-500">Accepted: JPG, PNG, WEBP (max 4MB)</p>
                        <div id="selectedFileWrap" class="hidden mt-2 flex items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-2">
                                <div class="min-w-0">
                                    <p id="selectedFileName" class="text-xs font-medium text-slate-700 truncate"></p>
                                    <p id="selectedFileMeta" class="text-[11px] text-slate-500"></p>
                                </div>
                                <button type="button" id="clearPrescriptionButton" class="px-2 py-1 text-[11px] font-medium border border-slate-200 text-slate-600 rounded-md hover:bg-slate-50 transition">Remove</button>
                           </div>

                           <img src="" alt="Prescription preview" id="preview" class="hidden border border-slate-200 rounded-lg mt-2 object-cover" width="100px">
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
                    enctype="multipart/form-data"
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

                    <div>
                        <label for="update-location-image" class="mb-1 block text-sm font-medium text-slate-700">Replace Location Image</label>
                        <input id="update-location-image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus:border-slate-400 focus:outline-none">
                        <p class="mt-1 text-[11px] text-slate-500">Leave empty to keep current image.</p>
                        <div id="updateSelectedFileWrap" class="hidden mt-2 flex items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-2">
                            <div class="min-w-0">
                                <p id="updateSelectedFileName" class="text-xs font-medium text-slate-700 truncate"></p>
                                <p id="updateSelectedFileMeta" class="text-[11px] text-slate-500"></p>
                            </div>
                            <button type="button" id="clearUpdateImageButton" class="px-2 py-1 text-[11px] font-medium border border-slate-200 text-slate-600 rounded-md hover:bg-slate-50 transition">Remove</button>
                        </div>
                        <img src="" alt="Image preview" id="updatePreview" class="hidden border border-slate-200 rounded-lg mt-2 object-cover" width="100px">
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
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h3 class="text-lg font-semibold text-slate-900">Campus Map Preview</h3>
                    <div class="flex items-center gap-2">
                        <button type="button" id="preview-satellite-toggle" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            Satellite
                        </button>
                        <button type="button" id="preview-fullscreen-toggle" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            Fullscreen
                        </button>
                        <button type="button" class="text-slate-400 hover:text-slate-700" onclick="document.getElementById('campus-map-preview-modal').classList.add('hidden')">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
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
            border-radius: 14px;
            padding: 0;
        }

        .leaflet-popup-content {
            margin: 0;
            width: 280px !important;
        }

        .map-place-popup {
            overflow: hidden;
            border-radius: 14px;
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
                'image_url' => $location->image_url,
            ];
        })->values();
    @endphp
    <script>
        const campusLocations = @json($campusLocations);

        let previewMap = null;
        let previewLayer = null;
        let previewDefaultTiles = null;
        let previewSatelliteTiles = null;
        let previewSatelliteLabelTiles = null;
        let previewSatelliteEnabled = false;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function resolveLocationImage(location) {
            const image = String(location.image_url || '').trim();
            if (image !== '') {
                return image;
            }

            return 'https://picsum.photos/seed/campus-default/480/260';
        }

        function markerPopupHtml(location) {
            const category = location.category || 'Campus Place';
            const description = location.description || 'No description available.';
            const imageUrl = resolveLocationImage(location);

            return `
                <div class="map-place-popup">
                    <img class="map-place-popup__image" src="${imageUrl}" alt="${escapeHtml(location.name)}" loading="lazy">
                    <div class="map-place-popup__body">
                        <h4 class="map-place-popup__title">${escapeHtml(location.name)}</h4>
                        <span class="map-place-popup__category">${escapeHtml(category)}</span>
                        <div class="map-place-popup__description">${escapeHtml(description)}</div>
                        <div class="map-place-popup__coords">${Number(location.latitude).toFixed(5)}, ${Number(location.longitude).toFixed(5)}</div>
                    </div>
                </div>
            `;
        }

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
                const updateImageInput = document.getElementById('update-location-image');
                if (updateImageInput) {
                    updateImageInput.value = '';
                }

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

                previewDefaultTiles = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20,
                    subdomains: 'abcd',
                    attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                });

                previewSatelliteTiles = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: 'Tiles &copy; Esri',
                });

                previewSatelliteLabelTiles = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                    maxZoom: 19,
                    attribution: 'Labels &copy; Esri',
                    pane: 'overlayPane',
                });

                previewDefaultTiles.addTo(previewMap);

                previewLayer = L.layerGroup().addTo(previewMap);
            }

            previewLayer.clearLayers();

            if (campusLocations.length > 0) {
                const bounds = [];

                campusLocations.forEach(function (location) {
                    const latLng = [location.latitude, location.longitude];
                    bounds.push(latLng);

                    L.marker(latLng)
                        .bindPopup(markerPopupHtml(location))
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

        function togglePreviewSatellite() {
            const button = document.getElementById('preview-satellite-toggle');

            if (!previewMap || !previewDefaultTiles || !previewSatelliteTiles || !previewSatelliteLabelTiles || !button) {
                return;
            }

            previewSatelliteEnabled = !previewSatelliteEnabled;

            if (previewSatelliteEnabled) {
                previewMap.removeLayer(previewDefaultTiles);
                previewSatelliteTiles.addTo(previewMap);
                previewSatelliteLabelTiles.addTo(previewMap);
                button.textContent = 'Map';
            } else {
                previewMap.removeLayer(previewSatelliteTiles);
                previewMap.removeLayer(previewSatelliteLabelTiles);
                previewDefaultTiles.addTo(previewMap);
                button.textContent = 'Satellite';
            }
        }

        function togglePreviewFullscreen() {
            const container = document.getElementById('campus-preview-map');

            if (!container) {
                return;
            }

            if (document.fullscreenElement) {
                document.exitFullscreen();
                return;
            }

            if (container.requestFullscreen) {
                container.requestFullscreen();
            }
        }

        const previewButton = document.getElementById('open-map-preview');
        if (previewButton) {
            previewButton.addEventListener('click', function () {
                document.getElementById('campus-map-preview-modal').classList.remove('hidden');
                initPreviewMap();
            });
        }

        const previewSatelliteButton = document.getElementById('preview-satellite-toggle');
        if (previewSatelliteButton) {
            previewSatelliteButton.addEventListener('click', togglePreviewSatellite);
        }

        const previewFullscreenButton = document.getElementById('preview-fullscreen-toggle');
        if (previewFullscreenButton) {
            previewFullscreenButton.addEventListener('click', togglePreviewFullscreen);
        }

        document.addEventListener('fullscreenchange', function () {
            setTimeout(function () {
                if (previewMap) {
                    previewMap.invalidateSize();
                }
            }, 80);
        });

        // image preview section
        function toFileSize(bytes) {
            if (!bytes) return '0 KB';
            return bytes < 1024 * 1024
                ? `${Math.max(1, Math.round(bytes / 1024))} KB`
                : `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
        }

        function initImagePreview(inputId, previewId, fileWrapId, fileNameId, fileMetaId, clearBtnId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            const fileWrap = document.getElementById(fileWrapId);
            const fileName = document.getElementById(fileNameId);
            const fileMeta = document.getElementById(fileMetaId);
            const clearBtn = document.getElementById(clearBtnId);

            if (!input || !preview || !fileWrap || !fileName || !fileMeta || !clearBtn) return;

            let objectUrl = null;

            function reset(clearInput = false) {
                if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
                preview.src = '';
                preview.classList.add('hidden');
                fileWrap.classList.add('hidden');
                fileName.textContent = '';
                fileMeta.textContent = '';
                if (clearInput) input.value = '';
            }

            function apply(file) {
                if (!file) { reset(); return; }
                reset();
                fileName.textContent = file.name;
                fileMeta.textContent = `${file.type || 'Image'} - ${toFileSize(file.size)}`;
                fileWrap.classList.remove('hidden');
                objectUrl = URL.createObjectURL(file);
                preview.src = objectUrl;
                preview.classList.remove('hidden');
            }

            input.addEventListener('change', function () { apply(this.files[0] || null); });
            clearBtn.addEventListener('click', function () { reset(true); });
        }

        initImagePreview('location-image', 'preview', 'selectedFileWrap', 'selectedFileName', 'selectedFileMeta', 'clearPrescriptionButton');
        initImagePreview('update-location-image', 'updatePreview', 'updateSelectedFileWrap', 'updateSelectedFileName', 'updateSelectedFileMeta', 'clearUpdateImageButton');

        // reset update preview when edit modal opens
        document.querySelectorAll('[data-edit-map]').forEach(function (button) {
            button.addEventListener('click', function () {
                const updatePreview = document.getElementById('updatePreview');
                const updateFileWrap = document.getElementById('updateSelectedFileWrap');
                if (updatePreview) { updatePreview.src = ''; updatePreview.classList.add('hidden'); }
                if (updateFileWrap) updateFileWrap.classList.add('hidden');
            });
        });
    </script>
@endpush